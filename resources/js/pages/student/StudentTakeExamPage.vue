<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import RadioButton from 'primevue/radiobutton';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const loading = ref(true);
const saving = ref(false);
const submitting = ref(false);
const payload = ref(null);
const answers = ref({});
const timeRemaining = ref(0);
let timer = null;

const examId = computed(() => route.params.examId);

function formatTimer(seconds) {
    const min = Math.floor(seconds / 60).toString().padStart(2, '0');
    const sec = (seconds % 60).toString().padStart(2, '0');
    return `${min}:${sec}`;
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get(`/student/exams/${examId.value}/take`);
        payload.value = data.data;
        timeRemaining.value = data.data.time_remaining;

        const initAnswers = {};
        data.data.questions.forEach((question) => {
            if (question.question_type === 'essay') {
                initAnswers[question.id] = data.data.saved_essay_answers?.[question.id] || '';
            } else {
                initAnswers[question.id] = data.data.saved_answers?.[question.id] || null;
            }
        });
        answers.value = initAnswers;

        startTimer();
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được bài thi', life: 2200 });
        router.push('/app/student/exams');
    } finally {
        loading.value = false;
    }
}

function startTimer() {
    stopTimer();
    timer = setInterval(async () => {
        if (timeRemaining.value <= 0) {
            stopTimer();
            await submitExam();
            return;
        }
        timeRemaining.value -= 1;
    }, 1000);
}

function stopTimer() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}

async function autosave() {
    saving.value = true;
    try {
        await api.post(`/student/exams/${examId.value}/autosave`, { answers: answers.value });
        toast.add({ severity: 'success', summary: 'Đã lưu tạm', life: 1200 });
    } catch {
        toast.add({ severity: 'error', summary: 'Không thể lưu tạm', life: 1800 });
    } finally {
        saving.value = false;
    }
}

async function submitExam() {
    if (submitting.value) return;
    submitting.value = true;
    stopTimer();
    try {
        const { data } = await api.post(`/student/exams/${examId.value}/submit`, { answers: answers.value });
        toast.add({ severity: 'success', summary: 'Nộp bài thành công', life: 1800 });
        router.push(data.data.redirect);
    } catch {
        toast.add({ severity: 'error', summary: 'Không thể nộp bài', life: 2200 });
        startTimer();
    } finally {
        submitting.value = false;
    }
}

onMounted(fetchData);
onUnmounted(stopTimer);
</script>

<template>
    <AppShell>
        <Card v-if="loading">
            <template #content>
                <div class="flex justify-center py-8"><ProgressSpinner /></div>
            </template>
        </Card>

        <Card v-else>
            <template #title>
                <div class="flex items-center justify-between">
                    <span>{{ payload.exam.title }}</span>
                    <span class="rounded bg-red-100 px-3 py-1 font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-300">
                        {{ formatTimer(timeRemaining) }}
                    </span>
                </div>
            </template>
            <template #content>
                <div class="space-y-4">
                    <div v-for="(question, index) in payload.questions" :key="question.id" class="rounded border border-slate-200 p-4 dark:border-slate-700">
                        <div class="mb-2 font-semibold">Câu {{ index + 1 }}: {{ question.question_text }}</div>
                        <div class="mb-3 text-sm text-slate-500">Điểm: {{ question.points }}</div>

                        <div v-if="question.question_type === 'essay'">
                            <Textarea v-model="answers[question.id]" rows="4" fluid />
                        </div>
                        <div v-else class="space-y-2">
                            <label v-for="answer in question.answers" :key="answer.id" class="flex items-center gap-2">
                                <RadioButton v-model="answers[question.id]" :input-id="`answer-${question.id}-${answer.id}`" :value="answer.id" />
                                <span>{{ answer.answer_text }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <Button label="Lưu tạm" severity="secondary" icon="pi pi-save" :loading="saving" @click="autosave" />
                        <Button label="Nộp bài" icon="pi pi-check" :loading="submitting" @click="submitExam" />
                    </div>
                </div>
            </template>
        </Card>
    </AppShell>
</template>

