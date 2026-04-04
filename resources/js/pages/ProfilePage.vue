<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';

import AppShell from '../layouts/AppShell.vue';
import api from '../services/api';
import { getRoleLabel } from '../utils/roleLabels';

const toast = useToast();
const profileLoading = ref(false);
const passwordLoading = ref(false);

const profileForm = reactive({
    name: '',
    username: '',
    role: '',
});

const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const roleDisplay = computed(() => getRoleLabel(profileForm.role));

async function fetchProfile() {
    const { data } = await api.get('/profile');
    profileForm.name = data.data.name;
    profileForm.username = data.data.username;
    profileForm.role = data.data.role;
}

async function updateProfile() {
    profileLoading.value = true;
    try {
        await api.put('/profile', { name: profileForm.name });
        toast.add({ severity: 'success', summary: 'Cập nhật hồ sơ thành công', life: 1800 });
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể cập nhật hồ sơ', detail: error.response?.data?.message, life: 2200 });
    } finally {
        profileLoading.value = false;
    }
}

async function updatePassword() {
    passwordLoading.value = true;
    try {
        await api.put('/profile/password', passwordForm);
        passwordForm.current_password = '';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';
        toast.add({ severity: 'success', summary: 'Đổi mật khẩu thành công', life: 1800 });
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể đổi mật khẩu', detail: error.response?.data?.message, life: 2200 });
    } finally {
        passwordLoading.value = false;
    }
}

onMounted(fetchProfile);
</script>

<template>
    <AppShell>
        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <template #title>Thông tin cá nhân</template>
                <template #content>
                    <form class="space-y-3" @submit.prevent="updateProfile">
                        <div class="flex flex-col gap-2">
                            <label class="font-medium">Tên</label>
                            <InputText v-model="profileForm.name" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-medium">Tên đăng nhập</label>
                            <InputText :model-value="profileForm.username" disabled />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-medium">Vai trò</label>
                            <InputText :model-value="roleDisplay" disabled />
                        </div>
                        <Button type="submit" label="Lưu thay đổi" :loading="profileLoading" />
                    </form>
                </template>
            </Card>

            <Card>
                <template #title>Đổi mật khẩu</template>
                <template #content>
                    <form class="space-y-3" @submit.prevent="updatePassword">
                        <div class="flex flex-col gap-2">
                            <label class="font-medium">Mật khẩu hiện tại</label>
                            <Password v-model="passwordForm.current_password" :feedback="false" toggle-mask fluid />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-medium">Mật khẩu mới</label>
                            <Password v-model="passwordForm.password" :feedback="false" toggle-mask fluid />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-medium">Xác nhận mật khẩu mới</label>
                            <Password v-model="passwordForm.password_confirmation" :feedback="false" toggle-mask fluid />
                        </div>
                        <Button type="submit" label="Cập nhật mật khẩu" :loading="passwordLoading" />
                    </form>
                </template>
            </Card>
        </div>
    </AppShell>
</template>


