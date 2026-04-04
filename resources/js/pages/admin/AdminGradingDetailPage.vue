<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const userExamId = computed(() => route.params.userExamId);

const loading = ref(true);
const saving = ref(false);
const userExam = ref(null);
const essayItems = ref([]);
const grades = reactive({});

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get(`/admin/grading/${userExamId.value}`);
        userExam.value = data.data;

        essayItems.value = (userExam.value.user_answers || []).filter((item) => item.question?.question_type === 'essay');

        essayItems.value.forEach((item) => {
            grades[item.question_id] = {
                score: item.essay_score ?? 0,
                feedback: item.admin_feedback || '',
            };
        });
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được dữ liệu chấm bài', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function submitGrades() {
    saving.value = true;
    try {
        await api.post(`/admin/grading/${userExamId.value}`, { grades });
        toast.add({ severity: 'success', summary: 'Lưu kết quả chấm bài thành công', life: 1800 });
        router.push(`/app/admin/grading/exams/${userExam.value.exam_id}/users`);
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể lưu kết quả chấm bài', detail: error.response?.data?.message, life: 2200 });
    } finally {
        saving.value = false;
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card v-if="userExam">
            <template #title>Chấm bài: {{ userExam.user?.name }} - {{ userExam.exam?.title }}</template>
            <template #content>
                <div v-if="loading" class="py-6 text-center">Đang tải dữ liệu...</div>
                <div v-else class="space-y-4">
                    <div v-for="item in essayItems" :key="item.id" class="rounded border border-slate-200 p-4 dark:border-slate-700">
                        <div class="mb-2 font-semibold">{{ item.question?.question_text }}</div>
                        <div class="mb-3 whitespace-pre-wrap rounded bg-slate-100 p-3 text-sm dark:bg-slate-800">{{ item.essay_answer }}</div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label class="font-medium">Điểm</label>
                                <InputNumber v-model="grades[item.question_id].score" :min="0" :max="item.question?.points || 10" :use-grouping="false" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-medium">Nhận xét</label>
                                <Textarea v-model="grades[item.question_id].feedback" rows="3" />
                            </div>
                        </div>
                    </div>

                    <Button label="Lưu chấm bài" icon="pi pi-save" :loading="saving" @click="submitGrades" />
                </div>
            </template>
        </Card>
    </AppShell>
</template>

