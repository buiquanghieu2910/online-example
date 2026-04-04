<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import Checkbox from 'primevue/checkbox';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const router = useRouter();
const toast = useToast();
const loading = ref(true);
const dialogVisible = ref(false);
const saving = ref(false);
const editingId = ref(null);

const exams = ref([]);
const classes = ref([]);

const form = reactive({
    title: '', description: '', duration: 60, pass_score: 60,
    is_active: true, start_time: null, end_time: null, class_id: null,
});

const classOptions = computed(() => classes.value.map((item) => ({ label: item.name, value: item.id })));

function resetForm() {
    editingId.value = null;
    form.title = '';
    form.description = '';
    form.duration = 60;
    form.pass_score = 60;
    form.is_active = true;
    form.start_time = null;
    form.end_time = null;
    form.class_id = null;
}

function openCreate() {
    resetForm();
    dialogVisible.value = true;
}

function openEdit(item) {
    resetForm();
    editingId.value = item.id;
    form.title = item.title;
    form.description = item.description || '';
    form.duration = item.duration;
    form.pass_score = item.pass_score;
    form.is_active = item.is_active;
    form.start_time = item.start_time ? new Date(item.start_time) : null;
    form.end_time = item.end_time ? new Date(item.end_time) : null;
    form.class_id = item.class_id;
    dialogVisible.value = true;
}

function openQuestions(item) {
    router.push(`/app/teacher/exams/${item.id}/questions`);
}

function openAssign(item) {
    router.push(`/app/teacher/exams/${item.id}/assign`);
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/teacher/exams');
        exams.value = data.data.exams;
        classes.value = data.data.classes;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được bài thi', life: 2200 });
    } finally {
        loading.value = false;
    }
}

function toIso(value) {
    return value ? new Date(value).toISOString() : null;
}

async function submitForm() {
    saving.value = true;
    try {
        const payload = {
            ...form,
            start_time: toIso(form.start_time),
            end_time: toIso(form.end_time),
        };

        if (editingId.value) {
            await api.put(`/teacher/exams/${editingId.value}`, payload);
            toast.add({ severity: 'success', summary: 'Cập nhật bài thi thành công', life: 1800 });
        } else {
            await api.post('/teacher/exams', payload);
            toast.add({ severity: 'success', summary: 'Tạo bài thi thành công', life: 1800 });
        }

        dialogVisible.value = false;
        await fetchData();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể lưu bài thi', detail: error.response?.data?.message, life: 2200 });
    } finally {
        saving.value = false;
    }
}

async function removeExam(item) {
    if (!window.confirm(`Xóa bài thi ${item.title}?`)) return;
    try {
        await api.delete(`/teacher/exams/${item.id}`);
        toast.add({ severity: 'success', summary: 'Xóa bài thi thành công', life: 1800 });
        await fetchData();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể xóa bài thi', detail: error.response?.data?.message, life: 2200 });
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>
                <div class="flex items-center justify-between">
                    <span>Bài thi của tôi</span>
                    <Button label="Tạo bài thi" icon="pi pi-plus" @click="openCreate" />
                </div>
            </template>
            <template #content>
                <DataTable :value="exams" :loading="loading" paginator :rows="10" striped-rows>
                    <Column field="title" header="Tiêu đề" />
                    <Column field="class_name" header="Lớp" />
                    <Column field="duration" header="Thời gian" />
                    <Column field="questions_count" header="Câu hỏi" />
                    <Column header="Trạng thái">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.is_active ? 'Đang mở' : 'Đóng'" :severity="slotProps.data.is_active ? 'success' : 'secondary'" />
                        </template>
                    </Column>
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <div class="flex gap-1">
                                <Button icon="pi pi-list" text @click="openQuestions(slotProps.data)" />
                                <Button icon="pi pi-users" text @click="openAssign(slotProps.data)" />
                                <Button icon="pi pi-pencil" text @click="openEdit(slotProps.data)" />
                                <Button icon="pi pi-trash" text severity="danger" @click="removeExam(slotProps.data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog v-model:visible="dialogVisible" :header="editingId ? 'Cập nhật bài thi' : 'Tạo bài thi'" modal :style="{ width: '42rem' }">
            <form class="space-y-3" @submit.prevent="submitForm">
                <InputText v-model="form.title" placeholder="Tiêu đề" required />
                <Textarea v-model="form.description" rows="3" placeholder="Mô tả" />
                <Select v-model="form.class_id" :options="classOptions" option-label="label" option-value="value" placeholder="Chọn lớp" />
                <div class="grid gap-3 md:grid-cols-2">
                    <InputNumber v-model="form.duration" :min="1" :use-grouping="false" />
                    <InputNumber v-model="form.pass_score" :min="0" :use-grouping="false" />
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <DatePicker v-model="form.start_time" show-time hour-format="24" fluid />
                    <DatePicker v-model="form.end_time" show-time hour-format="24" fluid />
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox v-model="form.is_active" binary input-id="teacher-exam-active" />
                    <label for="teacher-exam-active">Đang hoạt động</label>
                </div>
                <div class="flex justify-end gap-2">
                    <Button type="button" label="Hủy" severity="secondary" text @click="dialogVisible = false" />
                    <Button type="submit" label="Lưu" :loading="saving" />
                </div>
            </form>
        </Dialog>
    </AppShell>
</template>

