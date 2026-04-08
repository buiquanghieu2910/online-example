<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import Tag from 'primevue/tag';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const route = useRoute();
const toast = useToast();
const loading = ref(true);
const result = ref(null);

const resultId = computed(() => route.params.resultId);

const scoreText = computed(() => {
    if (!result.value) return '-';
    if (result.value.score === null || result.value.score === undefined) return 'Đang chờ chấm';
    return `${result.value.score}`;
});

const passScoreText = computed(() => {
    if (!result.value) return '-';
    return `${result.value.pass_score ?? result.value.exam?.pass_score ?? 0}`;
});

const resultLabel = computed(() => {
    if (!result.value) return '-';
    if (result.value.is_passed === null) return 'Đang chờ';
    return result.value.is_passed ? 'Đạt' : 'Không đạt';
});

const resultSeverity = computed(() => {
    if (!result.value) return 'secondary';
    if (result.value.is_passed === null) return 'secondary';
    return result.value.is_passed ? 'success' : 'danger';
});

function gradingLabel(status) {
    if (status === 'pending_review') return 'Chờ chấm';
    if (status === 'manually_graded') return 'Đã chấm thủ công';
    if (status === 'auto_graded') return 'Đã chấm tự động';
    return status;
}

function gradingSeverity(status) {
    if (status === 'pending_review') return 'warn';
    if (status === 'manually_graded') return 'info';
    if (status === 'auto_graded') return 'success';
    return 'secondary';
}

function questionResult(item) {
    if (item.question?.question_type === 'essay') {
        if (item.essay_score === null || item.essay_score === undefined) {
            return { label: 'Đang chờ chấm', severity: 'secondary' };
        }
        return { label: `Điểm: ${item.essay_score}/${item.question?.points ?? 0}`, severity: 'info' };
    }

    if (item.is_correct === true) {
        return { label: 'Đúng', severity: 'success' };
    }

    if (item.is_correct === false) {
        return { label: 'Sai', severity: 'danger' };
    }

    return { label: 'Chưa chấm', severity: 'secondary' };
}

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
            <template #content>
                <div v-if="loading" class="py-6 text-center">Đang tải...</div>
                <div v-else class="space-y-4">
                    <div class="grid gap-3 md:grid-cols-4">
                        <div class="rounded border border-slate-200 p-3 dark:border-slate-700">
                            <div class="text-xs text-slate-500">Điểm</div>
                            <div class="text-lg font-semibold">{{ scoreText }}</div>
                        </div>
                        <div class="rounded border border-slate-200 p-3 dark:border-slate-700">
                            <div class="text-xs text-slate-500">Điểm đạt</div>
                            <div class="text-lg font-semibold">{{ passScoreText }}</div>
                        </div>
                        <div class="rounded border border-slate-200 p-3 dark:border-slate-700">
                            <div class="text-xs text-slate-500">Kết luận</div>
                            <Tag :value="resultLabel" :severity="resultSeverity" />
                        </div>
                        <div class="rounded border border-slate-200 p-3 dark:border-slate-700">
                            <div class="text-xs text-slate-500">Trạng thái chấm</div>
                            <Tag :value="gradingLabel(result.grading_status)" :severity="gradingSeverity(result.grading_status)" />
                        </div>
                    </div>

                    <div class="rounded border border-slate-200 p-4 dark:border-slate-700">
                        <div class="text-sm text-slate-600 dark:text-slate-300">
                            Số câu đã làm: {{ result.question_count }} | Câu trắc nghiệm đúng: {{ result.correct_count }} | Tổng điểm câu hỏi: {{ result.total_points }}
                        </div>
                    </div>

                    <div v-for="item in result.user_answers" :key="item.id">
                        <div class="flex items-start justify-between gap-3">
                            <div class="font-semibold">{{ item.question?.question_text }}</div>
                            <Tag :value="questionResult(item).label" :severity="questionResult(item).severity" />
                        </div>
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
