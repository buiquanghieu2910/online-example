<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const router = useRouter();
const toast = useToast();
const loading = ref(true);
const exams = ref([]);

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/student/exams');
        exams.value = data.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được danh sách bài thi', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function startExam(item) {
    try {
        const { data } = await api.post(`/student/exams/${item.id}/start`);
        router.push(data.data.redirect);
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể bắt đầu bài thi', detail: error.response?.data?.message, life: 2200 });
    }
}

function continueExam(item) {
    router.push(`/app/student/exams/${item.id}/take`);
}

function viewResult(item) {
    if (!item.latest_completed_attempt_id) return;
    router.push(`/app/student/results/${item.latest_completed_attempt_id}`);
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>Bài thi của tôi</template>
            <template #content>
                <DataTable :value="exams" :loading="loading" paginator :rows="10" striped-rows>
                    <Column field="title" header="Tiêu đề" />
                    <Column field="duration" header="Thời gian (phút)" />
                    <Column field="latest_score" header="Điểm gần nhất" />
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button v-if="slotProps.data.in_progress_attempt_id" label="Tiếp tục" size="small" @click="continueExam(slotProps.data)" />
                                <Button v-else-if="slotProps.data.has_new_attempt" label="Bắt đầu" size="small" @click="startExam(slotProps.data)" />
                                <Button v-else-if="slotProps.data.latest_completed_attempt_id" label="Xem kết quả" severity="secondary" size="small" @click="viewResult(slotProps.data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </AppShell>
</template>

