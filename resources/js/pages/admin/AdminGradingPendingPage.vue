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
        const { data } = await api.get('/admin/grading/pending');
        exams.value = data.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được danh sách chờ chấm', life: 2200 });
    } finally {
        loading.value = false;
    }
}

function openExam(item) {
    router.push(`/app/admin/grading/exams/${item.id}/users`);
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>Bài thi đang chờ chấm</template>
            <template #content>
                <DataTable :value="exams" :loading="loading" striped-rows>
                    <Column field="title" header="Bài thi" />
                    <Column field="pending_count" header="Số bài chờ chấm" />
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <Button label="Xem danh sách" text @click="openExam(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </AppShell>
</template>

