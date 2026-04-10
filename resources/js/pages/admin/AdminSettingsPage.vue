<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const toast = useToast();
const loading = ref(true);
const saving = ref(false);

const maintenance = reactive({
    enabled: false,
    message: 'Hệ thống đang bảo trì. Vui lòng quay lại sau.',
});

async function fetchMaintenance() {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/maintenance');
        maintenance.enabled = Boolean(data.data?.enabled);
        maintenance.message = data.data?.message || maintenance.message;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được cài đặt', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function saveMaintenance() {
    saving.value = true;
    try {
        const { data } = await api.put('/admin/maintenance', {
            enabled: maintenance.enabled,
            message: maintenance.message,
        });
        maintenance.enabled = Boolean(data.data?.enabled);
        maintenance.message = data.data?.message || maintenance.message;
        toast.add({ severity: 'success', summary: data.message || 'Đã lưu cài đặt', life: 2200 });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Lưu cài đặt thất bại',
            detail: error.response?.data?.message || 'Không thể cập nhật chế độ bảo trì.',
            life: 2600,
        });
    } finally {
        saving.value = false;
    }
}

onMounted(fetchMaintenance);
</script>

<template>
    <AppShell>
        <div class="mb-4 text-2xl font-semibold text-slate-700 dark:text-slate-100">Cài đặt hệ thống</div>

        <Card>
            <template #title>Chế độ bảo trì</template>
            <template #content>
                <div v-if="loading" class="flex justify-center py-10">
                    <ProgressSpinner style="width: 44px; height: 44px" />
                </div>
                <div v-else class="grid gap-3 md:grid-cols-[180px_1fr_auto] md:items-end">
                    <div class="flex items-center gap-2">
                        <Checkbox v-model="maintenance.enabled" binary input-id="maintenance-enabled" />
                        <label for="maintenance-enabled" class="text-sm font-medium">Bật bảo trì</label>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="maintenance-message" class="text-sm font-medium">Thông báo hiển thị</label>
                        <InputText id="maintenance-message" v-model="maintenance.message" maxlength="255" />
                    </div>
                    <Button label="Lưu" icon="pi pi-save" :loading="saving" @click="saveMaintenance" />
                </div>
            </template>
        </Card>
    </AppShell>
</template>

