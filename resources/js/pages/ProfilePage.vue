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
const passwordErrors = reactive({
    current_password: [],
    password: [],
    password_confirmation: [],
});

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

function clearPasswordErrors() {
    passwordErrors.current_password = [];
    passwordErrors.password = [];
    passwordErrors.password_confirmation = [];
}

function applyPasswordErrors(errors = {}) {
    passwordErrors.current_password = errors.current_password || [];
    passwordErrors.password = errors.password || [];
    passwordErrors.password_confirmation = errors.password_confirmation || [];
}

function firstPasswordError(field) {
    return passwordErrors[field]?.[0] || '';
}

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
    clearPasswordErrors();

    try {
        await api.put('/profile/password', passwordForm);
        passwordForm.current_password = '';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';
        toast.add({ severity: 'success', summary: 'Đổi mật khẩu thành công', life: 1800 });
    } catch (error) {
        const apiErrors = error.response?.data?.errors || {};
        applyPasswordErrors(apiErrors);

        const firstError =
            firstPasswordError('current_password') ||
            firstPasswordError('password') ||
            firstPasswordError('password_confirmation') ||
            error.response?.data?.message;

        toast.add({ severity: 'error', summary: 'Không thể đổi mật khẩu', detail: firstError, life: 2200 });
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
                            <Password
                                v-model="passwordForm.current_password"
                                :feedback="false"
                                toggle-mask
                                fluid
                                :invalid="Boolean(firstPasswordError('current_password'))"
                            />
                            <small v-if="firstPasswordError('current_password')" class="text-red-500">
                                {{ firstPasswordError('current_password') }}
                            </small>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-medium">Mật khẩu mới</label>
                            <Password
                                v-model="passwordForm.password"
                                :feedback="false"
                                toggle-mask
                                fluid
                                :invalid="Boolean(firstPasswordError('password'))"
                            />
                            <small v-if="firstPasswordError('password')" class="text-red-500">
                                {{ firstPasswordError('password') }}
                            </small>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-medium">Xác nhận mật khẩu mới</label>
                            <Password
                                v-model="passwordForm.password_confirmation"
                                :feedback="false"
                                toggle-mask
                                fluid
                                :invalid="Boolean(firstPasswordError('password_confirmation'))"
                            />
                            <small v-if="firstPasswordError('password_confirmation')" class="text-red-500">
                                {{ firstPasswordError('password_confirmation') }}
                            </small>
                        </div>
                        <Button type="submit" label="Cập nhật mật khẩu" :loading="passwordLoading" />
                    </form>
                </template>
            </Card>
        </div>
    </AppShell>
</template>
