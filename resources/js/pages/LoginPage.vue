<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Popover from 'primevue/popover';

import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';

const authStore = useAuthStore();
const themeStore = useThemeStore();
const router = useRouter();
const route = useRoute();
const toast = useToast();

const loading = ref(false);
const colorPopover = ref();

const form = reactive({
    username: '',
    password: '',
    remember: false,
});
const formErrors = reactive({
    username: [],
    password: [],
});

const themeIcon = computed(() => (themeStore.isDark ? 'pi pi-sun' : 'pi pi-moon'));
const themeTooltip = computed(() => (themeStore.isDark ? 'Chuyển sang giao diện sáng' : 'Chuyển sang giao diện tối'));
const primeColorTooltip = computed(() => `Màu chủ đạo hiện tại: ${themeStore.primeColor}`);

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

function handleThemeToggle() {
    themeStore.toggleTheme();
}

function togglePrimeColorPopover(event) {
    colorPopover.value?.toggle(event);
}

function selectPrimeColor(color) {
    themeStore.setPrimeColor(color);
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
    <div
        class="relative flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-100 via-slate-50 to-white px-4 transition-colors dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
        <div class="absolute right-4 top-4 z-10 flex items-center gap-2 rounded-xl border border-slate-200 bg-white/90 p-1.5 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/85">
            <Button
                icon="pi pi-palette"
                :aria-label="primeColorTooltip"
                v-tooltip.bottom="primeColorTooltip"
                outlined
                size="small"
                class="h-9! w-9! rounded-lg!"
                @click="togglePrimeColorPopover" />
            <Button
                :icon="themeIcon"
                :aria-label="themeTooltip"
                v-tooltip.bottom="themeTooltip"
                outlined
                size="small"
                class="h-9! w-9! rounded-lg!"
                @click="handleThemeToggle" />
        </div>

        <Popover ref="colorPopover">
            <div class="w-[220px] space-y-3 p-1">
                <div class="text-sm font-medium">Chọn màu chủ đạo</div>
                <div class="grid grid-cols-8 gap-2">
                    <button
                        v-for="color in themeStore.primeColorOptions"
                        :key="color"
                        type="button"
                        class="h-6 w-6 rounded-full border-2 transition"
                        :class="themeStore.primeColor === color ? 'border-slate-900 dark:border-white' : 'border-transparent'"
                        :style="{ backgroundColor: themeStore.primePaletteMap[color]?.[500] || '#64748b' }"
                        :aria-label="`Chọn màu ${color}`"
                        @click="selectPrimeColor(color)" />
                </div>
            </div>
        </Popover>

        <Card class="w-full max-w-md shadow-xl ring-1 ring-slate-200/60 dark:ring-slate-700/70">
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
                            :invalid="Boolean(firstError('password'))" />
                        <small v-if="firstError('password')" class="text-red-500">{{ firstError('password') }}</small>
                    </div>

                    <Button type="submit" label="Đăng nhập" :loading="loading" class="w-full" />
                </form>
            </template>
        </Card>
    </div>
</template>
