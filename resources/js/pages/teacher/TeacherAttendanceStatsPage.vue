<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const toast = useToast();
const loading = ref(true);
const classes = ref([]);
const students = ref([]);
const stats = ref(null);
const attendances = ref([]);
const selectedStudent = ref(null);

const filters = reactive({
    month: new Date().toISOString().slice(0, 7),
    class_id: null,
    student_id: null,
});

function monthToInputValue(value) {
    return value || new Date().toISOString().slice(0, 7);
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/teacher/attendances/statistics', {
            params: {
                month: filters.month,
                class_id: filters.class_id,
                student_id: filters.student_id,
            },
        });

        classes.value = data.data.classes;
        students.value = data.data.students;
        stats.value = data.data.stats;
        attendances.value = data.data.attendances;
        selectedStudent.value = data.data.student;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được thống kê điểm danh', life: 2200 });
    } finally {
        loading.value = false;
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>Thống kê điểm danh</template>
            <template #content>
                <div class="mb-4 grid gap-3 md:grid-cols-4">
                    <InputText v-model="filters.month" type="month" :model-value="monthToInputValue(filters.month)" @update:model-value="(v) => filters.month = v" />
                    <Select v-model="filters.class_id" :options="classes" option-label="name" option-value="id" placeholder="Tất cả lớp" show-clear />
                    <Select v-model="filters.student_id" :options="students" option-label="name" option-value="id" placeholder="Chọn học sinh" show-clear />
                    <Button label="Xem thống kê" icon="pi pi-search" :loading="loading" @click="fetchData" />
                </div>

                <div v-if="stats" class="mb-4 grid gap-3 md:grid-cols-5">
                    <Card><template #title>Có mặt</template><template #content>{{ stats.present }}</template></Card>
                    <Card><template #title>Vắng</template><template #content>{{ stats.absent }}</template></Card>
                    <Card><template #title>Muộn</template><template #content>{{ stats.late }}</template></Card>
                    <Card><template #title>Có phép</template><template #content>{{ stats.excused }}</template></Card>
                    <Card><template #title>Tổng</template><template #content>{{ stats.total }}</template></Card>
                </div>

                <div v-if="selectedStudent" class="mb-2 font-medium">Chi tiết: {{ selectedStudent.name }}</div>
                <div class="space-y-2">
                    <div v-for="item in attendances" :key="item.id" class="rounded border border-slate-200 p-3 dark:border-slate-700">
                        <div>{{ item.date }} - {{ item.status }}</div>
                        <div v-if="item.notes" class="text-sm text-slate-500">{{ item.notes }}</div>
                    </div>
                </div>
            </template>
        </Card>
    </AppShell>
</template>

