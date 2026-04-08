<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';

import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';
import { getRoleLabel } from '../utils/roleLabels';

const authStore = useAuthStore();
const themeStore = useThemeStore();
const router = useRouter();
const route = useRoute();
const toast = useToast();

const isSidebarOpen = ref(false);
const isDesktopCollapsed = ref(false);
const tooltip = ref({ visible: false, label: '', top: 0, left: 0 });
const confirmLogoutVisible = ref(false);

const menuItems = computed(() => {
    const role = authStore.user?.role;

    if (role === 'admin') {
        return [
            { label: 'Bảng điều khiển', icon: 'pi pi-home', to: '/app/admin/dashboard' },
            { label: 'Người dùng', icon: 'pi pi-users', to: '/app/admin/users' },
            { label: 'Lớp học', icon: 'pi pi-sitemap', to: '/app/admin/classes' },
            { label: 'Bài thi', icon: 'pi pi-file-edit', to: '/app/admin/exams' },
            { label: 'Chấm bài', icon: 'pi pi-check-square', to: '/app/admin/grading/pending' },
            { label: 'Giám sát thi', icon: 'pi pi-eye', to: '/app/admin/monitor' },
        ];
    }

    if (role === 'teacher') {
        return [
            { label: 'Bảng điều khiển', icon: 'pi pi-home', to: '/app/teacher/dashboard' },
            { label: 'Lớp học', icon: 'pi pi-sitemap', to: '/app/teacher/classes' },
            { label: 'Học sinh', icon: 'pi pi-users', to: '/app/teacher/students' },
            { label: 'Bài thi', icon: 'pi pi-file-edit', to: '/app/teacher/exams' },
            { label: 'Điểm danh', icon: 'pi pi-calendar', to: '/app/teacher/attendances' },
            { label: 'Thống kê điểm danh', icon: 'pi pi-chart-bar', to: '/app/teacher/attendances/statistics' },
            { label: 'Giám sát thi', icon: 'pi pi-eye', to: '/app/teacher/monitor' },
        ];
    }

    if (role === 'student') {
        return [
            { label: 'Bảng điều khiển', icon: 'pi pi-home', to: '/app/student/dashboard' },
            { label: 'Bài thi của tôi', icon: 'pi pi-file', to: '/app/student/exams' },
            { label: 'Kết quả', icon: 'pi pi-chart-line', to: '/app/student/results' },
        ];
    }

    return [{ label: 'Trang đăng nhập', icon: 'pi pi-sign-in', to: '/app/login' }];
});

const roleLabel = computed(() => getRoleLabel(authStore.user?.role));
const pageTitle = computed(() => {
    const current = menuItems.value.find((item) => isActive(item.to));
    return current?.label || 'Hệ thống thi trực tuyến';
});
const themeIcon = computed(() => (themeStore.isDark ? 'pi pi-sun' : 'pi pi-moon'));
const themeTooltip = computed(() => (themeStore.isDark ? 'Chuyển sang giao diện sáng' : 'Chuyển sang giao diện tối'));

const initials = computed(() => {
    if (!authStore.user?.name) {
        return 'U';
    }

    return authStore.user.name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();
});

function isActive(path) {
    return route.path === path || route.path.startsWith(path + '/');
}

function navigate(path) {
    hideTooltip();
    router.push(path);
    isSidebarOpen.value = false;
}

function handleMenuToggle() {
    if (window.matchMedia('(min-width: 768px)').matches) {
        isDesktopCollapsed.value = !isDesktopCollapsed.value;
        if (!isDesktopCollapsed.value) {
            hideTooltip();
        }
        return;
    }

    hideTooltip();
    isSidebarOpen.value = !isSidebarOpen.value;
}

function showTooltip(event, label) {
    if (!isDesktopCollapsed.value || !window.matchMedia('(min-width: 768px)').matches) {
        return;
    }

    const rect = event.currentTarget.getBoundingClientRect();
    tooltip.value = {
        visible: true,
        label,
        top: rect.top + rect.height / 2,
        left: rect.right + 12,
    };
}

function hideTooltip() {
    tooltip.value.visible = false;
}

function requestLogout() {
    hideTooltip();
    confirmLogoutVisible.value = true;
}

function cancelLogout() {
    confirmLogoutVisible.value = false;
}

async function handleLogout() {
    confirmLogoutVisible.value = false;
    await authStore.logout();
    toast.add({ severity: 'success', summary: 'Đã đăng xuất', life: 1800 });
    router.push('/app/login');
}

function handleThemeToggle() {
    themeStore.toggleTheme();
}

watch(
    () => route.path,
    () => hideTooltip()
);
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-800 transition-colors dark:bg-slate-950 dark:text-slate-100">
        <div v-if="isSidebarOpen" class="fixed inset-0 z-30 bg-black/40 md:hidden" @click="isSidebarOpen = false" />

        <aside
            class="fixed inset-y-0 left-0 z-40 transform border-r border-slate-200 bg-white transition-all dark:border-slate-800 dark:bg-slate-900"
            :class="[
                isDesktopCollapsed ? 'w-20' : 'w-72',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            ]">
            <div class="flex h-full flex-col">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div class="text-lg font-semibold" :class="isDesktopCollapsed ? 'text-center text-sm' : ''">Online Exam</div>
                    <div v-if="!isDesktopCollapsed" class="text-sm text-slate-500 dark:text-slate-300">{{ roleLabel }}</div>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto overflow-x-hidden p-3">
                    <button
                        v-for="item in menuItems"
                        :key="item.to"
                        type="button"
                        class="flex items-center rounded-xl text-left text-sm font-medium transition"
                        :class="[
                            isDesktopCollapsed ? 'mx-auto h-11 w-11 justify-center px-0' : 'w-full gap-3 px-3 py-2',
                            isActive(item.to)
                                ? 'bg-indigo-600 text-white shadow'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800',
                        ]"
                        @mouseenter="showTooltip($event, item.label)"
                        @mouseleave="hideTooltip"
                        @focus="showTooltip($event, item.label)"
                        @blur="hideTooltip"
                        @click="navigate(item.to)">
                        <i :class="item.icon" />
                        <span v-if="!isDesktopCollapsed">{{ item.label }}</span>
                    </button>
                </nav>

                <div class="border-t border-slate-200 p-3 dark:border-slate-800">
                    <button
                        type="button"
                        class="mb-3 flex w-full items-center gap-3 rounded-xl bg-slate-100 p-2 text-left transition hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700"
                        :class="isDesktopCollapsed ? 'justify-center' : ''"
                        @click="navigate('/app/profile')">
                        <Avatar :label="initials" shape="circle" />
                        <div v-if="!isDesktopCollapsed" class="min-w-0">
                            <div class="truncate text-sm font-semibold">{{ authStore.user?.name }}</div>
                            <div class="truncate text-xs text-slate-500 dark:text-slate-300">{{ authStore.user?.username }}</div>
                        </div>
                    </button>
                    <div class="flex items-center justify-center gap-2" :class="isDesktopCollapsed ? 'flex-col' : 'flex-row'">
                        <Button
                            :icon="themeIcon"
                            :aria-label="themeTooltip"
                            v-tooltip.top="themeTooltip"
                            outlined
                            size="small"
                            class="h-10! w-10! rounded-xl!"
                            @click="handleThemeToggle" />
                        <Button
                            icon="pi pi-sign-out"
                            v-tooltip.top="'Đăng xuất'"
                            severity="danger"
                            outlined
                            size="small"
                            class="h-10! w-10! rounded-xl!"
                            @click="requestLogout" />
                    </div>
                </div>
            </div>
        </aside>

        <div :class="isDesktopCollapsed ? 'md:pl-20' : 'md:pl-72'">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
                <div class="flex items-center gap-3">
                    <Button icon="pi pi-bars" v-tooltip.bottom="'Mở/thu gọn menu'" text @click="handleMenuToggle" />
                    <div class="text-base font-semibold md:text-lg">{{ pageTitle }}</div>
                </div>
            </header>

            <main class="p-4 md:p-8">
                <slot />
            </main>
        </div>

        <div
            v-if="tooltip.visible"
            class="pointer-events-none fixed z-100 -translate-y-1/2 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-2xl dark:bg-slate-700"
            :style="{ top: `${tooltip.top}px`, left: `${tooltip.left}px` }">
            {{ tooltip.label }}
        </div>

        <div v-if="confirmLogoutVisible" class="fixed inset-0 z-110 flex items-center justify-center bg-black/45 px-4" @click.self="cancelLogout">
            <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl dark:bg-slate-900">
                <div class="mb-2 text-lg font-semibold text-slate-900 dark:text-slate-100">Xác nhận đăng xuất</div>
                <p class="mb-5 text-sm text-slate-600 dark:text-slate-300">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống không?</p>
                <div class="flex justify-end gap-2">
                    <Button label="Hủy" severity="secondary" text @click="cancelLogout" />
                    <Button label="Đăng xuất" severity="danger" icon="pi pi-sign-out" @click="handleLogout" />
                </div>
            </div>
        </div>
    </div>
</template>











