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
const syncingTimer = ref(false);

let timer = null;
let deadlineAt = null;
let lastTimerSyncAt = 0;
let statusPollingTimer = null;

const examId = computed(() => route.params.examId);

function formatTimer(seconds) {
    const min = Math.floor(seconds / 60).toString().padStart(2, '0');
    const sec = (seconds % 60).toString().padStart(2, '0');
    return `${min}:${sec}`;
}

function setDeadline(remainingSeconds) {
    deadlineAt = Date.now() + Math.max(0, remainingSeconds) * 1000;
}

function getRemainingFromDeadline() {
    if (!deadlineAt) {
        return Math.max(0, timeRemaining.value);
    }

    return Math.max(0, Math.ceil((deadlineAt - Date.now()) / 1000));
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get(`/student/exams/${examId.value}/take`);
        payload.value = data.data;
        timeRemaining.value = data.data.time_remaining;
        setDeadline(timeRemaining.value);

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

    timer = setInterval(() => {
        const nextRemaining = getRemainingFromDeadline();
        timeRemaining.value = nextRemaining;

        if (nextRemaining <= 0) {
            stopTimer();
            submitExam();
            return;
        }

        if (Date.now() - lastTimerSyncAt >= 5000) {
            lastTimerSyncAt = Date.now();
            void syncTimer(false);
        }
    }, 1000);
}

function stopTimer() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}

async function syncTimer(force = false) {
    if (syncingTimer.value || !payload.value || (!force && submitting.value)) {
        return;
    }

    syncingTimer.value = true;
    try {
        const { data } = await api.post(`/student/exams/${examId.value}/timer`, {
            time_remaining: timeRemaining.value,
        });
        const serverRemaining = data?.data?.time_remaining;
        if (typeof serverRemaining === 'number') {
            timeRemaining.value = Math.max(0, serverRemaining);
            setDeadline(timeRemaining.value);
        }
    } catch {
        // Timer sync errors should not block exam flow.
    } finally {
        syncingTimer.value = false;
    }
}

async function autosave() {
    saving.value = true;
    try {
        await syncTimer();
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
        await syncTimer(true);
        const { data } = await api.post(`/student/exams/${examId.value}/submit`, { answers: answers.value });
        toast.add({ severity: 'success', summary: 'Nộp bài thành công', life: 1800 });
        router.push(data.data.redirect);
    } catch (error) {
        const statusData = await fetchAttemptStatus(false);
        if (statusData?.status === 'completed' && statusData.redirect) {
            toast.add({ severity: 'warn', summary: 'Bài thi đã được hệ thống tự nộp', life: 2200 });
            router.push(statusData.redirect);
            return;
        }

        toast.add({ severity: 'error', summary: 'Không thể nộp bài', detail: error.response?.data?.message, life: 2200 });
        setDeadline(timeRemaining.value);
        startTimer();
    } finally {
        submitting.value = false;
    }
}

async function fetchAttemptStatus(showSubmitToast = true) {
    if (!payload.value || submitting.value) {
        return null;
    }

    try {
        const { data } = await api.get(`/student/exams/${examId.value}/attempt-status`);
        const statusData = data.data;

        if (statusData.status === 'completed' && statusData.redirect) {
            stopTimer();
            if (showSubmitToast) {
                toast.add({ severity: 'warn', summary: 'Bài thi đã được hệ thống tự nộp', life: 2200 });
            }
            await router.push(statusData.redirect);
            return statusData;
        }

        if (statusData.status === 'in_progress' && typeof statusData.time_remaining === 'number') {
            timeRemaining.value = Math.min(timeRemaining.value, statusData.time_remaining);
            setDeadline(timeRemaining.value);
        }

        return statusData;
    } catch {
        return null;
    }
}

function handleVisibilityChange() {
    if (document.hidden) {
        void syncTimer(true);
        return;
    }

    timeRemaining.value = getRemainingFromDeadline();
    startTimer();
}

function handleBeforeUnload() {
    stopTimer();
    void syncTimer(true);
}

onMounted(() => {
    void fetchData();
    statusPollingTimer = setInterval(() => {
        void fetchAttemptStatus();
    }, 10000);
    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
    stopTimer();
    void syncTimer(true);
    if (statusPollingTimer) {
        clearInterval(statusPollingTimer);
        statusPollingTimer = null;
    }
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    window.removeEventListener('beforeunload', handleBeforeUnload);
});
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
