<script setup>
import { onMounted, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import AppShell from '../layouts/AppShell.vue';
import api from '../services/api';

const toast = useToast();
const loading = ref(true);
const exams = ref([]);

onMounted(async () => {
    try {
        const { data } = await api.get('/exams');
        exams.value = data.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được danh sách bài thi', life: 2200 });
    } finally {
        loading.value = false;
    }
});

function statusSeverity(isActive) {
    return isActive ? 'success' : 'secondary';
}
</script>

<template>
    <AppShell>
        <Card>
            <template #title>Danh sách bài thi</template>
            <template #content>
                <DataTable :value="exams" :loading="loading" paginator :rows="10" striped-rows>
                    <Column field="title" header="Tiêu đề" />
                    <Column field="duration" header="Thời gian (phút)" />
                    <Column field="pass_score" header="Điểm đạt" />
                    <Column field="start_time" header="Bắt đầu" />
                    <Column field="end_time" header="Kết thúc" />
                    <Column header="Trạng thái">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.is_active ? 'Đang mở' : 'Đóng'" :severity="statusSeverity(slotProps.data.is_active)" />
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </AppShell>
</template>


