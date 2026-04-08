<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const examId = computed(() => route.params.examId);

const loading = ref(true);
const resettingId = ref(null);
const exam = ref({ id: null, title: '' });
const users = ref([]);
const pendingCount = ref(0);

const timelineVisible = ref(false);
const timelineLoading = ref(false);
const timelineAttempt = ref(null);
const timelineItems = ref([]);

function statusLabel(status) {
    if (status === 'in_progress') return 'Đang làm';
    if (status === 'completed') return 'Đã nộp';
    if (status === 'not_started') return 'Chưa bắt đầu';
    return 'Chưa bắt đầu';
}

function statusSeverity(status) {
    if (status === 'in_progress') return 'warn';
    if (status === 'completed') return 'success';
    return 'secondary';
}

function gradingLabel(status) {
    if (status === 'pending_review') return 'Chờ chấm';
    if (status === 'manually_graded') return 'Đã chấm';
    if (status === 'auto_graded') return 'Tự chấm';
    return '-';
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

function formatDateTime(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString('vi-VN');
}

function openGrading(item) {
    if (!item.latest_user_exam_id) return;
    router.push(`/app/admin/grading/${item.latest_user_exam_id}`);
}

async function openTimeline(item) {
    if (!item.latest_user_exam_id) return;

    timelineVisible.value = true;
    timelineLoading.value = true;
    timelineAttempt.value = null;
    timelineItems.value = [];

    try {
        const { data } = await api.get(`/admin/monitor/active-attempts/${item.latest_user_exam_id}/timeline`);
        timelineAttempt.value = data.data.attempt;
        timelineItems.value = data.data.timeline || [];
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được timeline làm bài', life: 2200 });
    } finally {
        timelineLoading.value = false;
    }
}

async function resetAttempt(item) {
    if (!item.latest_user_exam_id) return;
    if (!window.confirm(`Cho phép ${item.user.name} làm lại bài thi này?`)) {
        return;
    }

    resettingId.value = item.latest_user_exam_id;
    try {
        await api.post(`/admin/grading/${item.latest_user_exam_id}/reset`);
        toast.add({ severity: 'success', summary: 'Đã cho phép học sinh làm lại', life: 2000 });
        await fetchData();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Không thể cho làm lại',
            detail: error.response?.data?.message,
            life: 2200,
        });
    } finally {
        resettingId.value = null;
    }
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get(`/admin/grading/exams/${examId.value}/users`);
        exam.value = data.data.exam;
        users.value = data.data.users || [];
        pendingCount.value = data.data.pending_count || 0;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được danh sách học sinh', life: 2200 });
    } finally {
        loading.value = false;
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>
                <div class="flex items-center justify-between gap-3">
                    <span>Theo dõi bài thi - {{ exam.title }}</span>
                    <Tag :value="`Chờ chấm: ${pendingCount}`" severity="info" />
                </div>
            </template>
            <template #content>
                <DataTable :value="users" :loading="loading" striped-rows paginator :rows="15">
                    <Column field="user.name" header="Học sinh" />
                    <Column field="user.username" header="Tài khoản" />
                    <Column field="attempts_count" header="Số lần làm" />
                    <Column header="Trạng thái">
                        <template #body="slotProps">
                            <Tag :value="statusLabel(slotProps.data.status)" :severity="statusSeverity(slotProps.data.status)" />
                        </template>
                    </Column>
                    <Column header="Chấm bài">
                        <template #body="slotProps">
                            {{ gradingLabel(slotProps.data.grading_status) }}
                        </template>
                    </Column>
                    <Column field="score" header="Điểm" />
                    <Column header="Nộp lúc">
                        <template #body="slotProps">
                            {{ formatDateTime(slotProps.data.completed_at) }}
                        </template>
                    </Column>
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button
                                    v-if="slotProps.data.latest_user_exam_id"
                                    label="Timeline"
                                    text
                                    @click="openTimeline(slotProps.data)"
                                />
                                <Button
                                    v-if="slotProps.data.can_grade"
                                    label="Chấm bài"
                                    text
                                    @click="openGrading(slotProps.data)"
                                />
                                <Button
                                    v-if="slotProps.data.can_reset"
                                    label="Cho làm lại"
                                    severity="secondary"
                                    text
                                    :loading="resettingId === slotProps.data.latest_user_exam_id"
                                    @click="resetAttempt(slotProps.data)"
                                />
                            </div>
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
