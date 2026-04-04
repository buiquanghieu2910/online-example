<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Select from 'primevue/select';
import MultiSelect from 'primevue/multiselect';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const toast = useToast();
const loading = ref(true);
const dialogVisible = ref(false);
const saving = ref(false);
const editingUserId = ref(null);

const users = ref([]);
const teachers = ref([]);

const roleOptions = [
    { label: 'Quản trị viên', value: 'admin' },
    { label: 'Giáo viên', value: 'teacher' },
    { label: 'Học sinh', value: 'student' },
];

const form = reactive({
    name: '',
    username: '',
    role: 'student',
    password: '',
    password_confirmation: '',
    teacher_ids: [],
});

const dialogTitle = computed(() => (editingUserId.value ? 'Cập nhật người dùng' : 'Tạo người dùng'));

function resetForm() {
    form.name = '';
    form.username = '';
    form.role = 'student';
    form.password = '';
    form.password_confirmation = '';
    form.teacher_ids = [];
    editingUserId.value = null;
}

function openCreate() {
    resetForm();
    dialogVisible.value = true;
}

function openEdit(user) {
    resetForm();
    editingUserId.value = user.id;
    form.name = user.name;
    form.username = user.username;
    form.role = user.role;
    form.teacher_ids = user.teachers?.map((item) => item.id) || [];
    dialogVisible.value = true;
}

function roleSeverity(role) {
    if (role === 'admin') return 'danger';
    if (role === 'teacher') return 'info';
    return 'success';
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/users');
        users.value = data.data.users;
        teachers.value = data.data.teachers;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được danh sách người dùng', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function submitForm() {
    saving.value = true;
    try {
        if (editingUserId.value) {
            await api.put(`/admin/users/${editingUserId.value}`, form);
            toast.add({ severity: 'success', summary: 'Cập nhật người dùng thành công', life: 1800 });
        } else {
            await api.post('/admin/users', form);
            toast.add({ severity: 'success', summary: 'Tạo người dùng thành công', life: 1800 });
        }

        dialogVisible.value = false;
        await fetchData();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể lưu người dùng', detail: error.response?.data?.message, life: 2200 });
    } finally {
        saving.value = false;
    }
}

async function removeUser(user) {
    if (!window.confirm(`Bạn có chắc muốn xóa người dùng ${user.name}?`)) {
        return;
    }

    try {
        await api.delete(`/admin/users/${user.id}`);
        toast.add({ severity: 'success', summary: 'Xóa người dùng thành công', life: 1800 });
        await fetchData();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể xóa người dùng', detail: error.response?.data?.message, life: 2200 });
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>
                <div class="flex items-center justify-between gap-3">
                    <span>Quản lý người dùng</span>
                    <Button label="Tạo người dùng" icon="pi pi-plus" @click="openCreate" />
                </div>
            </template>
            <template #content>
                <DataTable :value="users" :loading="loading" paginator :rows="10" striped-rows>
                    <Column field="name" header="Tên" />
                    <Column field="username" header="Tên đăng nhập" />
                    <Column header="Vai trò">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.role" :severity="roleSeverity(slotProps.data.role)" />
                        </template>
                    </Column>
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button icon="pi pi-pencil" text @click="openEdit(slotProps.data)" />
                                <Button icon="pi pi-trash" text severity="danger" @click="removeUser(slotProps.data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog v-model:visible="dialogVisible" :header="dialogTitle" modal :style="{ width: '38rem' }">
            <form class="space-y-3" @submit.prevent="submitForm">
                <div class="flex flex-col gap-2">
                    <label class="font-medium">Tên</label>
                    <InputText v-model="form.name" required />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">Tên đăng nhập</label>
                    <InputText v-model="form.username" required />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">Vai trò</label>
                    <Select v-model="form.role" :options="roleOptions" option-label="label" option-value="value" />
                </div>
                <div v-if="form.role === 'student'" class="flex flex-col gap-2">
                    <label class="font-medium">Giáo viên phụ trách</label>
                    <MultiSelect v-model="form.teacher_ids" :options="teachers" option-label="name" option-value="id" display="chip" fluid />
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Mật khẩu {{ editingUserId ? '(để trống nếu không đổi)' : '' }}</label>
                        <Password v-model="form.password" :feedback="false" toggle-mask fluid />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium">Xác nhận mật khẩu</label>
                        <Password v-model="form.password_confirmation" :feedback="false" toggle-mask fluid />
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <Button type="button" label="Hủy" severity="secondary" text @click="dialogVisible = false" />
                    <Button type="submit" label="Lưu" :loading="saving" />
                </div>
            </form>
        </Dialog>
    </AppShell>
</template>

