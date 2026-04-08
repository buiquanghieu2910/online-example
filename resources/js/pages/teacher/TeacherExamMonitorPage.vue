<script setup>
import { onMounted, onUnmounted, reactive, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import ProgressBar from 'primevue/progressbar';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const toast = useToast();
const loading = ref(false);
const summary = ref({
    active_attempts: 0,
    active_students: 0,
    expiring_soon: 0,
});
const items = ref([]);
const hasLoadedOnce = ref(false);
const backgroundRefreshing = ref(false);
const filterOptions = reactive({
    classes: [],
    exams: [],
    students: [],
});
const filters = reactive({
    class_id: null,
    exam_id: null,
    student_id: null,
});

const timelineVisible = ref(false);
const timelineLoading = ref(false);
const timelineAttempt = ref(null);
const timelineItems = ref([]);

let refreshTimer = null;

function formatTimer(seconds) {
    const safe = Math.max(0, Number(seconds || 0));
    const min = Math.floor(safe / 60).toString().padStart(2, '0');
    const sec = (safe % 60).toString().padStart(2, '0');
    return `${min}:${sec}`;
}

function formatDateTime(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString('vi-VN');
}

function eventLabel(event) {
    if (event === 'exam_started') return 'Bắt đầu làm bài';
    if (event === 'exam_opened') return 'Mở trang làm bài';
    if (event === 'exam_autosaved') return 'Lưu tạm';
    if (event === 'exam_submitted') return 'Nộp bài';
    if (event === 'exam_auto_submitted') return 'Hệ thống tự nộp';
    return event || '-';
}

function formatMeta(meta) {
    if (!meta) return '-';
    if (meta.time_remaining !== undefined) return `Thời gian còn lại: ${meta.time_remaining}s`;
    if (meta.answered_count !== undefined) return `Số câu đã lưu: ${meta.answered_count}`;
    if (meta.score !== undefined) return `Điểm: ${meta.score ?? '-'}`;
    if (meta.reason !== undefined) return `Lý do: ${meta.reason}`;
    return JSON.stringify(meta);
}

function buildParams() {
    const params = {};
    if (filters.class_id) params.class_id = filters.class_id;
    if (filters.exam_id) params.exam_id = filters.exam_id;
    if (filters.student_id) params.student_id = filters.student_id;
    return params;
}

async function fetchData({ showErrorToast = true, showLoading = false } = {}) {
    const useInitialLoading = showLoading && !hasLoadedOnce.value;
    const useHeaderProgress = !useInitialLoading;

    if (useInitialLoading) {
        loading.value = true;
    } else if (useHeaderProgress) {
        backgroundRefreshing.value = true;
    }

    try {
        const { data } = await api.get('/teacher/monitor/active-attempts', { params: buildParams() });
        summary.value = data.data.summary;
        items.value = data.data.items;
        filterOptions.classes = (data.data.filters?.classes || []).map((item) => ({ label: item.name, value: item.id }));
        filterOptions.exams = (data.data.filters?.exams || []).map((item) => ({ label: item.title, value: item.id }));
        filterOptions.students = (data.data.filters?.students || []).map((item) => ({
            label: `${item.name} (${item.username})`,
            value: item.id,
        }));
        hasLoadedOnce.value = true;
    } catch {
        if (showErrorToast) {
            toast.add({ severity: 'error', summary: 'Không tải được dữ liệu giám sát', life: 2200 });
        }
    } finally {
        if (useInitialLoading) {
            loading.value = false;
        }
        if (useHeaderProgress) {
            backgroundRefreshing.value = false;
        }
    }
}

async function openTimeline(item) {
    timelineVisible.value = true;
    timelineLoading.value = true;
    timelineAttempt.value = null;
    timelineItems.value = [];

    try {
        const { data } = await api.get(`/teacher/monitor/active-attempts/${item.user_exam_id}/timeline`);
        timelineAttempt.value = data.data.attempt;
        timelineItems.value = data.data.timeline || [];
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được timeline làm bài', life: 2200 });
    } finally {
        timelineLoading.value = false;
    }
}

function clearFilters() {
    filters.class_id = null;
    filters.exam_id = null;
    filters.student_id = null;
    void fetchData({ showLoading: true });
}

onMounted(async () => {
    await fetchData({ showLoading: true });
    refreshTimer = setInterval(() => {
        void fetchData({ showErrorToast: false, showLoading: false });
    }, 10000);
});

onUnmounted(() => {
    if (refreshTimer) {
        clearInterval(refreshTimer);
        refreshTimer = null;
    }
});
</script>

<template>
    <AppShell>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold">Giám sát thi realtime</h2>
            <Button label="Làm mới" icon="pi pi-refresh" :loading="backgroundRefreshing" @click="fetchData({ showLoading: true })" />
        </div>

        <Card class="mb-4">
            <template #content>
                <div class="grid gap-3 md:grid-cols-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Lớp</label>
                        <Select v-model="filters.class_id" :options="filterOptions.classes" option-label="label" option-value="value" show-clear @change="fetchData({ showLoading: true })" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Bài thi</label>
                        <Select v-model="filters.exam_id" :options="filterOptions.exams" option-label="label" option-value="value" show-clear @change="fetchData({ showLoading: true })" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Học sinh</label>
                        <Select v-model="filters.student_id" :options="filterOptions.students" option-label="label" option-value="value" show-clear @change="fetchData({ showLoading: true })" />
                    </div>
                    <div class="flex items-end">
                        <Button label="Xóa lọc" severity="secondary" outlined class="w-full" @click="clearFilters" />
                    </div>
                </div>
            </template>
        </Card>

        <div class="mb-4 grid gap-3 md:grid-cols-3">
            <Card>
                <template #content>
                    <div class="text-sm text-slate-500">Đang làm bài</div>
                    <div class="text-2xl font-bold">{{ summary.active_attempts }}</div>
                </template>
            </Card>
            <Card>
                <template #content>
                    <div class="text-sm text-slate-500">Học sinh đang thi</div>
                    <div class="text-2xl font-bold">{{ summary.active_students }}</div>
                </template>
            </Card>
            <Card>
                <template #content>
                    <div class="text-sm text-slate-500">Sắp hết giờ (&lt;= 5 phút)</div>
                    <div class="text-2xl font-bold text-red-600">{{ summary.expiring_soon }}</div>
                </template>
            </Card>
        </div>

        <Card>
            <template #title>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span>Danh sách đang làm bài</span>
                        <small class="text-slate-500">Tự động cập nhật mỗi 10 giây</small>
                    </div>
                    <ProgressBar v-if="backgroundRefreshing" mode="indeterminate" style="height: 4px" />
                </div>
            </template>
            <template #content>
                <DataTable :value="items" :loading="loading && !hasLoadedOnce" paginator :rows="15" striped-rows>
                    <Column field="student.name" header="Học sinh" />
                    <Column field="student.username" header="Username" />
                    <Column field="exam.title" header="Bài thi" />
                    <Column field="exam.class_name" header="Lớp" />
                    <Column header="Bắt đầu lúc">
                        <template #body="slotProps">
                            {{ formatDateTime(slotProps.data.started_at) }}
                        </template>
                    </Column>
                    <Column header="Còn lại">
                        <template #body="slotProps">
                            <span :class="slotProps.data.time_remaining <= 300 ? 'font-semibold text-red-600' : 'font-semibold'">
                                {{ formatTimer(slotProps.data.time_remaining) }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Hoạt động gần nhất">
                        <template #body="slotProps">
                            {{ eventLabel(slotProps.data.latest_event) }}
                        </template>
                    </Column>
                    <Column header="Thời điểm">
                        <template #body="slotProps">
                            {{ formatDateTime(slotProps.data.latest_event_at) }}
                        </template>
                    </Column>
                    <Column header="Chi tiết">
                        <template #body="slotProps">
                            <Button label="Timeline" text @click="openTimeline(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog v-model:visible="timelineVisible" modal header="Timeline làm bài" :style="{ width: '64rem' }">
            <div v-if="timelineLoading" class="py-8 text-center">Đang tải timeline...</div>
            <div v-else class="space-y-4">
                <div v-if="timelineAttempt" class="grid gap-3 rounded border border-slate-200 p-3 md:grid-cols-2 dark:border-slate-700">
                    <div><strong>Học sinh:</strong> {{ timelineAttempt.student?.name }} ({{ timelineAttempt.student?.username }})</div>
                    <div><strong>Bài thi:</strong> {{ timelineAttempt.exam?.title }}</div>
                    <div><strong>Bắt đầu:</strong> {{ formatDateTime(timelineAttempt.started_at) }}</div>
                    <div><strong>Kết thúc:</strong> {{ formatDateTime(timelineAttempt.completed_at) }}</div>
                </div>

                <DataTable :value="timelineItems" striped-rows size="small">
                    <Column header="Thời điểm">
                        <template #body="slotProps">
                            {{ formatDateTime(slotProps.data.created_at) }}
                        </template>
                    </Column>
                    <Column header="Sự kiện">
                        <template #body="slotProps">
                            {{ eventLabel(slotProps.data.event) }}
                        </template>
                    </Column>
                    <Column header="Chi tiết">
                        <template #body="slotProps">
                            {{ formatMeta(slotProps.data.meta) }}
                        </template>
                    </Column>
                    <Column field="ip_address" header="IP" />
                </DataTable>
            </div>
        </Dialog>
    </AppShell>
</template>
