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

function severity(status) {
    if (status === 'manually_graded') return 'info';
    if (status === 'pending_review') return 'warning';
    return 'success';
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
                    <Column field="score" header="Điểm" />
                    <Column header="Trạng thái chấm">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.grading_status" :severity="severity(slotProps.data.grading_status)" />
                        </template>
                    </Column>
                    <Column field="completed_at" header="Thời gian hoàn thành" />
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

