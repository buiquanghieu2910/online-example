<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';

import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();
const toast = useToast();

const loading = ref(false);
const form = reactive({
    username: '',
    password: '',
    remember: false,
});
const formErrors = reactive({
    username: [],
    password: [],
});

function clearFormErrors() {
    formErrors.username = [];
    formErrors.password = [];
}

function applyFormErrors(errors = {}) {
    formErrors.username = errors.username || [];
    formErrors.password = errors.password || [];
}

function firstError(field) {
    return formErrors[field]?.[0] || '';
}

onMounted(() => {
    const message = sessionStorage.getItem('auth-expired-message');
    if (message) {
        toast.add({ severity: 'warn', summary: 'Vui lòng đăng nhập lại', detail: message, life: 2600 });
        sessionStorage.removeItem('auth-expired-message');
    }
});

async function submit() {
    loading.value = true;
    clearFormErrors();

    try {
        const homePath = await authStore.login(form);
        const redirectPath = route.query.redirect || homePath;
        toast.add({ severity: 'success', summary: 'Đăng nhập thành công', life: 1800 });
        router.push(redirectPath);
    } catch (error) {
        applyFormErrors(error.response?.data?.errors || {});
        const message = firstError('username') || firstError('password') || error.response?.data?.message || 'Sai tài khoản hoặc mật khẩu.';
        toast.add({ severity: 'error', summary: 'Đăng nhập thất bại', detail: message, life: 2200 });
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-100 px-4 transition-colors dark:bg-slate-950">
        <Card class="w-full max-w-md shadow-xl">
            <template #title>Đăng nhập hệ thống thi trực tuyến</template>
            <template #content>
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="flex flex-col gap-2">
                        <label for="username" class="font-medium">Tên đăng nhập</label>
                        <InputText id="username" v-model="form.username" autocomplete="username" required :invalid="Boolean(firstError('username'))" />
                        <small v-if="firstError('username')" class="text-red-500">{{ firstError('username') }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="password" class="font-medium">Mật khẩu</label>
                        <Password
                            id="password"
                            v-model="form.password"
                            :feedback="false"
                            toggle-mask
                            autocomplete="current-password"
                            required
                            fluid
                            :invalid="Boolean(firstError('password'))"
                        />
                        <small v-if="firstError('password')" class="text-red-500">{{ firstError('password') }}</small>
                    </div>

                    <Button type="submit" label="Đăng nhập" :loading="loading" class="w-full" />
                </form>
            </template>
        </Card>
    </div>
</template>
