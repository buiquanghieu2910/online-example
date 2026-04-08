<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import ProgressSpinner from 'primevue/progressspinner';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import AppShell from '../layouts/AppShell.vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const toast = useToast();
const router = useRouter();
const authStore = useAuthStore();
const loading = ref(true);
const stats = ref([]);
const alerts = ref([]);
const trend = ref({ series: [], max_attempts: 0 });
const questionQuality = ref({ hardest: [], easiest: [], most_blank: [] });
const classes = ref([]);
const exams = ref([]);

const filters = reactive({
    days: 14,
    class_id: null,
    exam_id: null,
});

const dayOptions = [
    { label: '7 ngày', value: 7 },
    { label: '14 ngày', value: 14 },
    { label: '30 ngày', value: 30 },
    { label: '60 ngày', value: 60 },
];

const isStudent = computed(() => authStore.user?.role === 'student');
const title = computed(() => {
    if (authStore.user?.role === 'admin') return 'Bảng điều khiển quản trị';
    if (authStore.user?.role === 'teacher') return 'Bảng điều khiển giáo viên';
    return 'Bảng điều khiển học sinh';
});

function severityForAlert(level) {
    if (level === 'danger') return 'danger';
    if (level === 'warn') return 'warn';
    if (level === 'info') return 'info';
    return 'secondary';
}

function barWidth(item) {
    const max = Math.max(1, Number(trend.value.max_attempts || 0));
    return `${Math.max(6, Math.round((Number(item.attempts || 0) / max) * 100))}%`;
}

function formatPercent(value) {
    if (value === null || value === undefined) return '-';
    return `${value}%`;
}

function formatScore(value) {
    if (value === null || value === undefined) return '-';
    return `${value}`;
}

function gotoAlert(item) {
    if (!item?.route) return;
    router.push(item.route);
}

async function fetchData() {
    loading.value = true;
    try {
        const params = {
            days: filters.days,
            class_id: filters.class_id || undefined,
            exam_id: filters.exam_id || undefined,
        };
        const { data } = await api.get('/dashboard/overview', { params });
        stats.value = data.data.stats || [];
        alerts.value = data.data.alerts || [];
        trend.value = data.data.trend || { series: [], max_attempts: 0 };
        questionQuality.value = data.data.question_quality || { hardest: [], easiest: [], most_blank: [] };

        const filterPayload = data.data.filters || {};
        classes.value = (filterPayload.classes || []).map((item) => ({ label: item.name, value: item.id }));
        exams.value = (filterPayload.exams || []).map((item) => ({ label: item.title, value: item.id }));
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được bảng điều khiển', life: 2200 });
    } finally {
        loading.value = false;
    }
}

function clearFilters() {
    filters.days = 14;
    filters.class_id = null;
    filters.exam_id = null;
    void fetchData();
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <div class="mb-4 text-2xl font-semibold text-slate-700 dark:text-slate-100">{{ title }}</div>

        <div v-if="loading" class="flex justify-center py-12">
            <ProgressSpinner style="width: 48px; height: 48px" />
        </div>

        <template v-else>
            <div class="grid gap-4 md:grid-cols-3">
                <Card v-for="item in stats" :key="item.key" class="shadow-sm">
                    <template #title>{{ item.label }}</template>
                    <template #content>
                        <div class="text-3xl font-bold text-primary">{{ item.value }}</div>
                    </template>
                </Card>
            </div>

            <template v-if="!isStudent">
                <Card class="mt-4">
                    <template #content>
                        <div class="grid gap-3 md:grid-cols-4">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Khoảng thời gian</label>
                                <Select v-model="filters.days" :options="dayOptions" option-label="label" option-value="value" @change="fetchData" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Lớp</label>
                                <Select v-model="filters.class_id" :options="classes" option-label="label" option-value="value" show-clear @change="fetchData" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Bài thi</label>
                                <Select v-model="filters.exam_id" :options="exams" option-label="label" option-value="value" show-clear @change="fetchData" />
                            </div>
                            <div class="flex items-end">
                                <Button label="Xóa lọc" severity="secondary" outlined class="w-full" @click="clearFilters" />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="mt-4">
                    <template #title>Cảnh báo cần xử lý</template>
                    <template #content>
                        <div v-if="alerts.length" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <button
                                v-for="item in alerts"
                                :key="item.key"
                                type="button"
                                class="rounded border border-slate-200 p-3 text-left transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                @click="gotoAlert(item)">
                                <div class="mb-2">
                                    <Tag :value="item.label" :severity="severityForAlert(item.severity)" />
                                </div>
                                <div class="text-2xl font-bold">{{ item.value }}</div>
                            </button>
                        </div>
                        <div v-else class="text-sm text-slate-500">Không có cảnh báo cần xử lý.</div>
                    </template>
                </Card>

                <Card class="mt-4">
                    <template #title>Xu hướng theo thời gian</template>
                    <template #content>
                        <div class="space-y-3">
                            <div v-for="item in trend.series" :key="item.date" class="space-y-1">
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ item.label }}</span>
                                    <span>Lượt thi: {{ item.attempts }} | Điểm TB: {{ formatScore(item.avg_score) }} | Tỷ lệ đạt: {{ formatPercent(item.pass_rate) }}</span>
                                </div>
                                <div class="h-2 rounded bg-slate-200 dark:bg-slate-700">
                                    <div class="h-2 rounded bg-primary" :style="{ width: barWidth(item) }" />
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <div class="mt-4 grid gap-4 xl:grid-cols-3">
                    <Card>
                        <template #title>Top câu khó</template>
                        <template #content>
                            <DataTable :value="questionQuality.hardest" size="small" striped-rows>
                                <Column field="exam_title" header="Bài thi" />
                                <Column field="attempts" header="Lượt" />
                                <Column field="correct_rate" header="% Đúng" />
                            </DataTable>
                        </template>
                    </Card>

                    <Card>
                        <template #title>Top câu dễ</template>
                        <template #content>
                            <DataTable :value="questionQuality.easiest" size="small" striped-rows>
                                <Column field="exam_title" header="Bài thi" />
                                <Column field="attempts" header="Lượt" />
                                <Column field="correct_rate" header="% Đúng" />
                            </DataTable>
                        </template>
                    </Card>

                    <Card>
                        <template #title>Câu bỏ trống nhiều</template>
                        <template #content>
                            <DataTable :value="questionQuality.most_blank" size="small" striped-rows>
                                <Column field="exam_title" header="Bài thi" />
                                <Column field="attempts" header="Lượt" />
                                <Column field="blank_rate" header="% Bỏ trống" />
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </template>
        </template>
    </AppShell>
</template>
