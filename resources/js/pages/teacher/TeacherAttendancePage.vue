<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const classes = ref([]);
const students = ref([]);

const form = reactive({
    date: new Date(),
    class_id: null,
    attendances: [],
});

const statusOptions = [
    { label: 'Có mặt', value: 'present' },
    { label: 'Vắng', value: 'absent' },
    { label: 'Muộn', value: 'late' },
    { label: 'Có phép', value: 'excused' },
];

function formatDate(date) {
    return new Date(date).toISOString().slice(0, 10);
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/teacher/attendances', {
            params: {
                date: formatDate(form.date),
                class_id: form.class_id,
            },
        });

        classes.value = data.data.classes;
        students.value = data.data.students;
        form.attendances = students.value.map((student) => {
            const existing = student.attendances?.[0];
            return {
                student_id: student.id,
                status: existing?.status || 'present',
                notes: existing?.notes || '',
            };
        });
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được dữ liệu điểm danh', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function saveAttendance() {
    saving.value = true;
    try {
        await api.post('/teacher/attendances', {
            date: formatDate(form.date),
            class_id: form.class_id,
            attendances: form.attendances,
        });
        toast.add({ severity: 'success', summary: 'Lưu điểm danh thành công', life: 1800 });
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể lưu điểm danh', detail: error.response?.data?.message, life: 2200 });
    } finally {
        saving.value = false;
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>Điểm danh</template>
            <template #content>
                <div class="mb-4 grid gap-3 md:grid-cols-3">
                    <DatePicker v-model="form.date" fluid />
                    <Select v-model="form.class_id" :options="classes" option-label="name" option-value="id" placeholder="Tất cả lớp" show-clear />
                    <Button label="Tải danh sách" icon="pi pi-refresh" @click="fetchData" :loading="loading" />
                </div>

                <DataTable :value="form.attendances" :loading="loading" striped-rows>
                    <Column header="Học sinh">
                        <template #body="slotProps">
                            {{ students[slotProps.index]?.name }}
                        </template>
                    </Column>
                    <Column header="Trạng thái">
                        <template #body="slotProps">
                            <Select v-model="slotProps.data.status" :options="statusOptions" option-label="label" option-value="value" />
                        </template>
                    </Column>
                    <Column header="Ghi chú">
                        <template #body="slotProps">
                            <InputText v-model="slotProps.data.notes" />
                        </template>
                    </Column>
                </DataTable>

                <div class="mt-4">
                    <Button label="Lưu điểm danh" icon="pi pi-save" :loading="saving" @click="saveAttendance" />
                </div>
            </template>
        </Card>
    </AppShell>
</template>

