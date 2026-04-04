<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Divider from 'primevue/divider';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const route = useRoute();
const toast = useToast();
const loading = ref(true);
const result = ref(null);

const resultId = computed(() => route.params.resultId);

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get(`/student/results/${resultId.value}`);
        result.value = data.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được chi tiết kết quả', life: 2200 });
    } finally {
        loading.value = false;
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card v-if="result">
            <template #title>Kết quả: {{ result.exam?.title }}</template>
            <template #subtitle>Điểm: {{ result.score ?? 'Đang chờ chấm' }}</template>
            <template #content>
                <div v-if="loading" class="py-6 text-center">Đang tải...</div>
                <div v-else class="space-y-4">
                    <div v-for="item in result.user_answers" :key="item.id">
                        <div class="font-semibold">{{ item.question?.question_text }}</div>
                        <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                            <template v-if="item.question?.question_type === 'essay'">
                                Bài làm: {{ item.essay_answer || '(trống)' }}
                            </template>
                            <template v-else>
                                Đáp án đã chọn: {{ item.answer?.answer_text || '(chưa chọn)' }}
                            </template>
                        </div>
                        <div v-if="item.admin_feedback" class="mt-1 text-sm text-indigo-600 dark:text-indigo-300">Nhận xét: {{ item.admin_feedback }}</div>
                        <Divider />
                    </div>
                </div>
            </template>
        </Card>
    </AppShell>
</template>

