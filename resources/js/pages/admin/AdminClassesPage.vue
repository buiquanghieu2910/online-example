<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
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
const editingClassId = ref(null);

const classes = ref([]);
const teachers = ref([]);
const students = ref([]);

const form = reactive({
    name: '',
    code: '',
    subject: '',
    description: '',
    start_year: new Date().getFullYear(),
    end_year: new Date().getFullYear(),
    is_active: true,
    teacher_ids: [],
    student_ids: [],
});

const dialogTitle = computed(() => (editingClassId.value ? 'Cập nhật lớp học' : 'Tạo lớp học'));

function resetForm() {
    form.name = '';
    form.code = '';
    form.subject = '';
    form.description = '';
    form.start_year = new Date().getFullYear();
    form.end_year = new Date().getFullYear();
    form.is_active = true;
    form.teacher_ids = [];
    form.student_ids = [];
    editingClassId.value = null;
}

function openCreate() {
    resetForm();
    dialogVisible.value = true;
}

function openEdit(item) {
    resetForm();
    editingClassId.value = item.id;
    form.name = item.name;
    form.code = item.code;
    form.subject = item.subject;
    form.description = item.description || '';
    form.start_year = item.start_year;
    form.end_year = item.end_year;
    form.is_active = item.is_active;
    form.teacher_ids = item.teachers?.map((teacher) => teacher.id) || [];
    form.student_ids = item.students?.map((student) => student.id) || [];
    dialogVisible.value = true;
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/classes');
        classes.value = data.data.classes;
        teachers.value = data.data.teachers;
        students.value = data.data.students;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được dữ liệu lớp học', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function submitForm() {
    saving.value = true;
    try {
        if (editingClassId.value) {
            await api.put(`/admin/classes/${editingClassId.value}`, form);
            toast.add({ severity: 'success', summary: 'Cập nhật lớp học thành công', life: 1800 });
        } else {
            await api.post('/admin/classes', form);
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
    if (!window.confirm(`Bạn có chắc muốn xóa lớp ${item.name}?`)) {
        return;
    }

    try {
        await api.delete(`/admin/classes/${item.id}`);
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
                <div class="flex items-center justify-between gap-3">
                    <span>Quản lý lớp học</span>
                    <Button label="Tạo lớp học" icon="pi pi-plus" @click="openCreate" />
                </div>
            </template>
            <template #content>
                <DataTable :value="classes" :loading="loading" paginator :rows="10" striped-rows>
                    <Column field="name" header="Tên lớp" />
                    <Column field="code" header="Mã lớp" />
                    <Column field="subject" header="Môn học" />
                    <Column field="start_year" header="Năm bắt đầu" />
                    <Column field="end_year" header="Năm kết thúc" />
                    <Column field="teachers_count" header="Giáo viên" />
                    <Column field="students_count" header="Học sinh" />
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

        <Dialog v-model:visible="dialogVisible" :header="dialogTitle" modal :style="{ width: '42rem' }">
            <form class="space-y-3" @submit.prevent="submitForm">
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Tên lớp</label>
                        <InputText v-model="form.name" required />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Mã lớp</label>
                        <InputText v-model="form.code" required />
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Môn học</label>
                        <InputText v-model="form.subject" required />
                    </div>
                    <div class="flex items-center gap-2 pt-7">
                        <Checkbox v-model="form.is_active" binary input-id="class-active" />
                        <label for="class-active">Đang hoạt động</label>
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Năm bắt đầu</label>
                        <InputNumber v-model="form.start_year" :use-grouping="false" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Năm kết thúc</label>
                        <InputNumber v-model="form.end_year" :use-grouping="false" />
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">Mô tả</label>
                    <Textarea v-model="form.description" rows="3" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">Giáo viên</label>
                    <MultiSelect v-model="form.teacher_ids" :options="teachers" option-label="name" option-value="id" display="chip" fluid />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">Học sinh</label>
                    <MultiSelect v-model="form.student_ids" :options="students" option-label="name" option-value="id" display="chip" filter fluid />
                </div>

                <div class="flex justify-end gap-2">
                    <Button type="button" label="Hủy" severity="secondary" text @click="dialogVisible = false" />
                    <Button type="submit" label="Lưu" :loading="saving" />
                </div>
            </form>
        </Dialog>
    </AppShell>
</template>

