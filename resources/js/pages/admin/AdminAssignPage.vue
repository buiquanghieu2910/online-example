<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Button from 'primevue/button';
import MultiSelect from 'primevue/multiselect';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const route = useRoute();
const toast = useToast();
const examId = computed(() => route.params.examId);

const loading = ref(true);
const saving = ref(false);
const exam = ref({ id: null, title: '' });
const students = ref([]);
const selectedIds = ref([]);

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get(`/admin/exams/${examId.value}/assign`);
        exam.value = data.data.exam;
        students.value = data.data.users;
        selectedIds.value = data.data.assigned_ids;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được dữ liệu phân công', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function saveAssign() {
    saving.value = true;
    try {
        await api.put(`/admin/exams/${examId.value}/assign`, { user_ids: selectedIds.value });
        toast.add({ severity: 'success', summary: 'Cập nhật phân công thành công', life: 1800 });
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể cập nhật phân công', detail: error.response?.data?.message, life: 2200 });
    } finally {
        saving.value = false;
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>Phân công học sinh - {{ exam.title }}</template>
            <template #content>
                <div class="space-y-4">
                    <MultiSelect v-model="selectedIds" :options="students" option-label="name" option-value="id" display="chip" filter fluid />
                    <Button label="Lưu phân công" icon="pi pi-save" :loading="saving || loading" @click="saveAssign" />
                </div>
            </template>
        </Card>
    </AppShell>
</template>

