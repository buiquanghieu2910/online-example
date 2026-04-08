<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const router = useRouter();
const toast = useToast();
const loading = ref(true);
const results = ref([]);

function gradingLabel(status) {
    if (status === 'pending_review') return 'Chờ chấm';
    if (status === 'manually_graded') return 'Đã chấm thủ công';
    if (status === 'auto_graded') return 'Đã chấm tự động';
    return status;
}

function gradingSeverity(status) {
    if (status === 'pending_review') return 'warn';
    if (status === 'manually_graded') return 'info';
    if (status === 'auto_graded') return 'success';
    return 'secondary';
}

function resultLabel(item) {
    if (item.is_passed === null) return 'Đang chờ';
    return item.is_passed ? 'Đạt' : 'Không đạt';
}

function resultSeverity(item) {
    if (item.is_passed === null) return 'secondary';
    return item.is_passed ? 'success' : 'danger';
}

function scoreLabel(item) {
    if (item.score === null || item.score === undefined) {
        return 'Đang chấm';
    }

    return `${item.score} / ${item.exam?.pass_score ?? 0}`;
}

function formatDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString('vi-VN');
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/student/results');
        results.value = data.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được kết quả', life: 2200 });
    } finally {
        loading.value = false;
    }
}

function openDetail(item) {
    router.push(`/app/student/results/${item.id}`);
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>Kết quả bài thi</template>
            <template #content>
                <DataTable :value="results" :loading="loading" paginator :rows="10" striped-rows>
                    <Column field="exam.title" header="Bài thi" />
                    <Column header="Điểm / Điểm đạt">
                        <template #body="slotProps">
                            {{ scoreLabel(slotProps.data) }}
                        </template>
                    </Column>
                    <Column header="Kết luận">
                        <template #body="slotProps">
                            <Tag :value="resultLabel(slotProps.data)" :severity="resultSeverity(slotProps.data)" />
                        </template>
                    </Column>
                    <Column header="Trạng thái chấm">
                        <template #body="slotProps">
                            <Tag :value="gradingLabel(slotProps.data.grading_status)" :severity="gradingSeverity(slotProps.data.grading_status)" />
                        </template>
                    </Column>
                    <Column header="Hoàn thành lúc">
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data.completed_at) }}
                        </template>
                    </Column>
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <Button label="Chi tiết" text @click="openDetail(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </AppShell>
</template>
