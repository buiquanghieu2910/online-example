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
import Button from 'primevue/button';
import Tag from 'primevue/tag';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const router = useRouter();
const toast = useToast();
const loading = ref(true);
const dialogVisible = ref(false);
const saving = ref(false);
const editingExamId = ref(null);

const exams = ref([]);

const form = reactive({
    title: '',
    description: '',
    duration: 60,
    pass_score: 60,
    is_active: true,
    start_time: null,
    end_time: null,
});

const dialogTitle = computed(() => (editingExamId.value ? 'Cập nhật bài thi' : 'Tạo bài thi'));

function resetForm() {
    form.title = '';
    form.description = '';
    form.duration = 60;
    form.pass_score = 60;
    form.is_active = true;
    form.start_time = null;
    form.end_time = null;
    editingExamId.value = null;
}

function openCreate() {
    resetForm();
    dialogVisible.value = true;
}

function openEdit(item) {
    resetForm();
    editingExamId.value = item.id;
    form.title = item.title;
    form.description = item.description || '';
    form.duration = item.duration;
    form.pass_score = item.pass_score;
    form.is_active = item.is_active;
    form.start_time = item.start_time ? new Date(item.start_time) : null;
    form.end_time = item.end_time ? new Date(item.end_time) : null;
    dialogVisible.value = true;
}

function openQuestions(item) {
    router.push(`/app/admin/exams/${item.id}/questions`);
}

function openAssign(item) {
    router.push(`/app/admin/exams/${item.id}/assign`);
}

function formatDateForApi(value) {
    return value ? new Date(value).toISOString() : null;
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/exams');
        exams.value = data.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được danh sách bài thi', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function submitForm() {
    saving.value = true;
    try {
        const payload = {
            ...form,
            start_time: formatDateForApi(form.start_time),
            end_time: formatDateForApi(form.end_time),
        };

        if (editingExamId.value) {
            await api.put(`/admin/exams/${editingExamId.value}`, payload);
            toast.add({ severity: 'success', summary: 'Cập nhật bài thi thành công', life: 1800 });
        } else {
            await api.post('/admin/exams', payload);
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
    if (!window.confirm(`Bạn có chắc muốn xóa bài thi ${item.title}?`)) {
        return;
    }

    try {
        await api.delete(`/admin/exams/${item.id}`);
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
                <div class="flex items-center justify-between gap-3">
                    <span>Quản lý bài thi</span>
                    <Button label="Tạo bài thi" icon="pi pi-plus" @click="openCreate" />
                </div>
            </template>
            <template #content>
                <DataTable :value="exams" :loading="loading" paginator :rows="10" striped-rows>
                    <Column field="title" header="Tiêu đề" />
                    <Column field="duration" header="Thời gian (phút)" />
                    <Column field="pass_score" header="Điểm đạt" />
                    <Column field="questions_count" header="Câu hỏi" />
                    <Column field="assigned_users_count" header="Được giao" />
                    <Column header="Trạng thái">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.is_active ? 'Đang mở' : 'Đóng'" :severity="slotProps.data.is_active ? 'success' : 'secondary'" />
                        </template>
                    </Column>
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <div class="flex flex-wrap gap-1">
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

        <Dialog v-model:visible="dialogVisible" :header="dialogTitle" modal :style="{ width: '42rem' }">
            <form class="space-y-3" @submit.prevent="submitForm">
                <div class="flex flex-col gap-2">
                    <label class="font-medium">Tiêu đề</label>
                    <InputText v-model="form.title" required />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">Mô tả</label>
                    <Textarea v-model="form.description" rows="3" />
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Thời gian làm bài (phút)</label>
                        <InputNumber v-model="form.duration" :min="1" :use-grouping="false" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Điểm đạt</label>
                        <InputNumber v-model="form.pass_score" :min="0" :use-grouping="false" />
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Bắt đầu</label>
                        <DatePicker v-model="form.start_time" show-time hour-format="24" fluid />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Kết thúc</label>
                        <DatePicker v-model="form.end_time" show-time hour-format="24" fluid />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox v-model="form.is_active" binary input-id="exam-active" />
                    <label for="exam-active">Đang hoạt động</label>
                </div>

                <div class="flex justify-end gap-2">
                    <Button type="button" label="Hủy" severity="secondary" text @click="dialogVisible = false" />
                    <Button type="submit" label="Lưu" :loading="saving" />
                </div>
            </form>
        </Dialog>
    </AppShell>
</template>

