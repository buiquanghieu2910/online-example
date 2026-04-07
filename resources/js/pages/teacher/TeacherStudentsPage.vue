<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const toast = useToast();
const loading = ref(true);
const dialogVisible = ref(false);
const saving = ref(false);
const editingId = ref(null);

const students = ref([]);
const form = reactive({
    name: '',
    username: '',
    password: '',
    password_confirmation: '',
});
const formErrors = reactive({
    name: [],
    username: [],
    password: [],
    password_confirmation: [],
});

function clearFormErrors() {
    formErrors.name = [];
    formErrors.username = [];
    formErrors.password = [];
    formErrors.password_confirmation = [];
}

function applyFormErrors(errors = {}) {
    formErrors.name = errors.name || [];
    formErrors.username = errors.username || [];
    formErrors.password = errors.password || [];
    formErrors.password_confirmation = errors.password_confirmation || [];
}

function firstError(field) {
    return formErrors[field]?.[0] || '';
}

function resetForm() {
    editingId.value = null;
    form.name = '';
    form.username = '';
    form.password = '';
    form.password_confirmation = '';
    clearFormErrors();
}

function openCreate() {
    resetForm();
    dialogVisible.value = true;
}

function openEdit(item) {
    resetForm();
    editingId.value = item.id;
    form.name = item.name;
    form.username = item.username;
    dialogVisible.value = true;
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/teacher/students');
        students.value = data.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được danh sách học sinh', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function submitForm() {
    saving.value = true;
    clearFormErrors();

    try {
        if (editingId.value) {
            await api.put(`/teacher/students/${editingId.value}`, form);
            toast.add({ severity: 'success', summary: 'Cập nhật học sinh thành công', life: 1800 });
        } else {
            await api.post('/teacher/students', form);
            toast.add({ severity: 'success', summary: 'Tạo học sinh thành công', life: 1800 });
        }
        dialogVisible.value = false;
        await fetchData();
    } catch (error) {
        applyFormErrors(error.response?.data?.errors || {});
        const detail =
            firstError('name') ||
            firstError('username') ||
            firstError('password') ||
            firstError('password_confirmation') ||
            error.response?.data?.message;
        toast.add({ severity: 'error', summary: 'Không thể lưu học sinh', detail, life: 2200 });
    } finally {
        saving.value = false;
    }
}

async function removeStudent(item) {
    if (!window.confirm(`Gỡ học sinh ${item.name}?`)) return;

    try {
        await api.delete(`/teacher/students/${item.id}`);
        toast.add({ severity: 'success', summary: 'Đã gỡ học sinh', life: 1800 });
        await fetchData();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể gỡ học sinh', detail: error.response?.data?.message, life: 2200 });
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>
                <div class="flex items-center justify-between">
                    <span>Học sinh phụ trách</span>
                    <Button label="Tạo học sinh" icon="pi pi-plus" @click="openCreate" />
                </div>
            </template>
            <template #content>
                <DataTable :value="students" :loading="loading" paginator :rows="10" striped-rows>
                    <Column field="name" header="Tên" />
                    <Column field="username" header="Tên đăng nhập" />
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button icon="pi pi-pencil" text @click="openEdit(slotProps.data)" />
                                <Button icon="pi pi-trash" text severity="danger" @click="removeStudent(slotProps.data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog v-model:visible="dialogVisible" :header="editingId ? 'Cập nhật học sinh' : 'Tạo học sinh'" modal :style="{ width: '36rem' }">
            <form class="space-y-3" @submit.prevent="submitForm">
                <div class="flex flex-col gap-2">
                    <InputText v-model="form.name" placeholder="Tên học sinh" required :invalid="Boolean(firstError('name'))" />
                    <small v-if="firstError('name')" class="text-red-500">{{ firstError('name') }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <InputText v-model="form.username" placeholder="Tên đăng nhập" required :invalid="Boolean(firstError('username'))" />
                    <small v-if="firstError('username')" class="text-red-500">{{ firstError('username') }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <Password
                        v-model="form.password"
                        :feedback="false"
                        toggle-mask
                        fluid
                        :placeholder="editingId ? 'Mật khẩu (để trống nếu không đổi)' : 'Mật khẩu'"
                        :invalid="Boolean(firstError('password'))"
                    />
                    <small v-if="firstError('password')" class="text-red-500">{{ firstError('password') }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <Password
                        v-model="form.password_confirmation"
                        :feedback="false"
                        toggle-mask
                        fluid
                        placeholder="Xác nhận mật khẩu"
                        :invalid="Boolean(firstError('password_confirmation'))"
                    />
                    <small v-if="firstError('password_confirmation')" class="text-red-500">{{ firstError('password_confirmation') }}</small>
                </div>
                <div class="flex justify-end gap-2">
                    <Button type="button" label="Hủy" severity="secondary" text @click="dialogVisible = false" />
                    <Button type="submit" label="Lưu" :loading="saving" />
                </div>
            </form>
        </Dialog>
    </AppShell>
</template>
