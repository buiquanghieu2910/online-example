<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const examId = computed(() => route.params.examId);

const loading = ref(true);
const exam = ref({ id: null, title: '' });
const userExams = ref([]);

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get(`/admin/grading/exams/${examId.value}/users`);
        exam.value = data.data.exam;
        userExams.value = data.data.user_exams;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được danh sách bài chờ chấm', life: 2200 });
    } finally {
        loading.value = false;
    }
}

function openGrading(item) {
    router.push(`/app/admin/grading/${item.id}`);
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>Danh sách bài chờ chấm - {{ exam.title }}</template>
            <template #content>
                <DataTable :value="userExams" :loading="loading" striped-rows>
                    <Column field="user.name" header="Học sinh" />
                    <Column field="user.username" header="Tên đăng nhập" />
                    <Column field="completed_at" header="Hoàn thành lúc" />
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <Button label="Chấm bài" text @click="openGrading(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </AppShell>
</template>

