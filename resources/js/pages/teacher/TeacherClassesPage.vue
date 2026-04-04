<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import MultiSelect from 'primevue/multiselect';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const toast = useToast();
const loading = ref(true);
const dialogVisible = ref(false);
const saving = ref(false);
const editingId = ref(null);

const classes = ref([]);
const students = ref([]);

const form = reactive({
    name: '', code: '', subject: '', description: '',
    start_year: new Date().getFullYear(),
    end_year: new Date().getFullYear(),
    is_active: true,
    student_ids: [],
});

function resetForm() {
    editingId.value = null;
    form.name = '';
    form.code = '';
    form.subject = '';
    form.description = '';
    form.start_year = new Date().getFullYear();
    form.end_year = new Date().getFullYear();
    form.is_active = true;
    form.student_ids = [];
}

function openCreate() {
    resetForm();
    dialogVisible.value = true;
}

function openEdit(item) {
    resetForm();
    editingId.value = item.id;
    form.name = item.name;
    form.code = item.code;
    form.subject = item.subject;
    form.description = item.description || '';
    form.start_year = item.start_year;
    form.end_year = item.end_year;
    form.is_active = item.is_active;
    form.student_ids = item.students?.map((student) => student.id) || [];
    dialogVisible.value = true;
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/teacher/classes');
        classes.value = data.data.classes;
        students.value = data.data.students;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được lớp học', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function submitForm() {
    saving.value = true;
    try {
        if (editingId.value) {
            await api.put(`/teacher/classes/${editingId.value}`, form);
            toast.add({ severity: 'success', summary: 'Cập nhật lớp học thành công', life: 1800 });
        } else {
            await api.post('/teacher/classes', form);
            toast.add({ severity: 'success', summary: 'Tạo lớp học thành công', life: 1800 });
        }
        dialogVisible.value = false;
        await fetchData();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể lưu lớp học', detail: error.response?.data?.message, life: 2200 });
    } finally {
        saving.value = false;
    }
}

async function removeClass(item) {
    if (!window.confirm(`Xóa lớp ${item.name}?`)) return;

    try {
        await api.delete(`/teacher/classes/${item.id}`);
        toast.add({ severity: 'success', summary: 'Xóa lớp học thành công', life: 1800 });
        await fetchData();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể xóa lớp học', detail: error.response?.data?.message, life: 2200 });
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>
                <div class="flex items-center justify-between">
                    <span>Lớp học của tôi</span>
                    <Button label="Tạo lớp" icon="pi pi-plus" @click="openCreate" />
                </div>
            </template>
            <template #content>
                <DataTable :value="classes" :loading="loading" paginator :rows="10" striped-rows>
                    <Column field="name" header="Tên lớp" />
                    <Column field="code" header="Mã lớp" />
                    <Column field="subject" header="Môn học" />
                    <Column field="students_count" header="Học sinh" />
                    <Column field="exams_count" header="Bài thi" />
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button icon="pi pi-pencil" text @click="openEdit(slotProps.data)" />
                                <Button icon="pi pi-trash" text severity="danger" @click="removeClass(slotProps.data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog v-model:visible="dialogVisible" :header="editingId ? 'Cập nhật lớp học' : 'Tạo lớp học'" modal :style="{ width: '42rem' }">
            <form class="space-y-3" @submit.prevent="submitForm">
                <div class="grid gap-3 md:grid-cols-2">
                    <InputText v-model="form.name" placeholder="Tên lớp" required />
                    <InputText v-model="form.code" placeholder="Mã lớp" required />
                </div>
                <InputText v-model="form.subject" placeholder="Môn học" required />
                <Textarea v-model="form.description" rows="3" placeholder="Mô tả" />
                <div class="grid gap-3 md:grid-cols-2">
                    <InputNumber v-model="form.start_year" :use-grouping="false" />
                    <InputNumber v-model="form.end_year" :use-grouping="false" />
                </div>
                <MultiSelect v-model="form.student_ids" :options="students" option-label="name" option-value="id" filter display="chip" fluid />
                <div class="flex items-center gap-2">
                    <Checkbox v-model="form.is_active" binary input-id="teacher-class-active" />
                    <label for="teacher-class-active">Đang hoạt động</label>
                </div>
                <div class="flex justify-end gap-2">
                    <Button type="button" label="Hủy" severity="secondary" text @click="dialogVisible = false" />
                    <Button type="submit" label="Lưu" :loading="saving" />
                </div>
            </form>
        </Dialog>
    </AppShell>
</template>

