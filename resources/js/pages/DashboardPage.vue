<script setup>
import { computed, onMounted, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import ProgressSpinner from 'primevue/progressspinner';

import AppShell from '../layouts/AppShell.vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const toast = useToast();
const authStore = useAuthStore();
const loading = ref(true);
const stats = ref([]);

const title = computed(() => {
    if (authStore.user?.role === 'admin') {
        return 'Bảng điều khiển quản trị';
    }

    if (authStore.user?.role === 'teacher') {
        return 'Bảng điều khiển giáo viên';
    }

    return 'Bảng điều khiển học sinh';
});

onMounted(async () => {
    try {
        const { data } = await api.get('/dashboard/overview');
        stats.value = data.data.stats;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được bảng điều khiển', life: 2200 });
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <AppShell>
        <div class="mb-4 text-2xl font-semibold text-slate-700 dark:text-slate-100">{{ title }}</div>

        <div v-if="loading" class="flex justify-center py-12">
            <ProgressSpinner style="width: 48px; height: 48px" />
        </div>

        <div v-else class="grid gap-4 md:grid-cols-3">
            <Card v-for="item in stats" :key="item.key" class="shadow-sm">
                <template #title>{{ item.label }}</template>
                <template #content>
                    <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-300">{{ item.value }}</div>
                </template>
            </Card>
        </div>
    </AppShell>
</template>



