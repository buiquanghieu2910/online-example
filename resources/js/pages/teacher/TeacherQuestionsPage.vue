<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';

import AppShell from '../../layouts/AppShell.vue';
import api from '../../services/api';

const route = useRoute();
const toast = useToast();
const examId = computed(() => route.params.examId);

const loading = ref(true);
const dialogVisible = ref(false);
const saving = ref(false);
const editingId = ref(null);
const exam = ref({ id: null, title: '' });
const questions = ref([]);

const typeOptions = [
    { label: 'Trắc nghiệm', value: 'multiple_choice' },
    { label: 'Đúng/Sai', value: 'true_false' },
    { label: 'Tự luận', value: 'essay' },
];

const form = reactive({
    question_text: '', question_type: 'multiple_choice', points: 1, order: 0,
    answers: [
        { answer_text: '', is_correct: true },
        { answer_text: '', is_correct: false },
    ],
});

function resetForm() {
    editingId.value = null;
    form.question_text = '';
    form.question_type = 'multiple_choice';
    form.points = 1;
    form.order = questions.value.length;
    form.answers = [
        { answer_text: '', is_correct: true },
        { answer_text: '', is_correct: false },
    ];
}

function ensureAnswers() {
    if (form.question_type === 'essay') {
        form.answers = [];
        return;
    }

    if (form.answers.length < 2) {
        form.answers = [
            { answer_text: '', is_correct: true },
            { answer_text: '', is_correct: false },
        ];
    }
}

function addAnswer() {
    form.answers.push({ answer_text: '', is_correct: false });
}

function removeAnswer(index) {
    if (form.answers.length <= 2) return;
    form.answers.splice(index, 1);
}

function openCreate() {
    resetForm();
    dialogVisible.value = true;
}

function openEdit(item) {
    resetForm();
    editingId.value = item.id;
    form.question_text = item.question_text;
    form.question_type = item.question_type;
    form.points = Number(item.points);
    form.order = item.order;
    form.answers = (item.answers || []).map((answer) => ({
        answer_text: answer.answer_text,
        is_correct: Boolean(answer.is_correct),
    }));
    ensureAnswers();
    dialogVisible.value = true;
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get(`/teacher/exams/${examId.value}/questions`);
        exam.value = data.data.exam;
        questions.value = data.data.questions;
    } catch {
        toast.add({ severity: 'error', summary: 'Không tải được câu hỏi', life: 2200 });
    } finally {
        loading.value = false;
    }
}

async function submitForm() {
    saving.value = true;
    try {
        const payload = {
            question_text: form.question_text,
            question_type: form.question_type,
            points: form.points,
            order: form.order,
            answers: form.question_type === 'essay' ? [] : form.answers,
        };

        if (editingId.value) {
            await api.put(`/teacher/questions/${editingId.value}`, payload);
            toast.add({ severity: 'success', summary: 'Cập nhật câu hỏi thành công', life: 1800 });
        } else {
            await api.post(`/teacher/exams/${examId.value}/questions`, payload);
            toast.add({ severity: 'success', summary: 'Tạo câu hỏi thành công', life: 1800 });
        }

        dialogVisible.value = false;
        await fetchData();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể lưu câu hỏi', detail: error.response?.data?.message, life: 2200 });
    } finally {
        saving.value = false;
    }
}

async function removeQuestion(item) {
    if (!window.confirm('Xóa câu hỏi này?')) return;
    try {
        await api.delete(`/teacher/questions/${item.id}`);
        toast.add({ severity: 'success', summary: 'Xóa câu hỏi thành công', life: 1800 });
        await fetchData();
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Không thể xóa câu hỏi', detail: error.response?.data?.message, life: 2200 });
    }
}

onMounted(fetchData);
</script>

<template>
    <AppShell>
        <Card>
            <template #title>
                <div class="flex items-center justify-between">
                    <span>Câu hỏi - {{ exam.title }}</span>
                    <Button label="Thêm câu hỏi" icon="pi pi-plus" @click="openCreate" />
                </div>
            </template>
            <template #content>
                <DataTable :value="questions" :loading="loading" paginator :rows="10" striped-rows>
                    <Column field="order" header="Thứ tự" />
                    <Column field="question_text" header="Nội dung" />
                    <Column field="points" header="Điểm" />
                    <Column field="question_type" header="Loại" />
                    <Column header="Thao tác">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button icon="pi pi-pencil" v-tooltip.top="'Cập nhật câu hỏi'" text @click="openEdit(slotProps.data)" />
                                <Button icon="pi pi-trash" v-tooltip.top="'Xóa câu hỏi'" text severity="danger" @click="removeQuestion(slotProps.data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog v-model:visible="dialogVisible" :header="editingId ? 'Cập nhật câu hỏi' : 'Thêm câu hỏi'" modal :style="{ width: '46rem' }">
            <form class="space-y-3" @submit.prevent="submitForm">
                <Textarea v-model="form.question_text" rows="3" placeholder="Nội dung câu hỏi" required />
                <div class="grid gap-3 md:grid-cols-3">
                    <Select v-model="form.question_type" :options="typeOptions" option-label="label" option-value="value" @change="ensureAnswers" />
                    <InputNumber v-model="form.points" :min="0.5" :step="0.5" :use-grouping="false" />
                    <InputNumber v-model="form.order" :min="0" :use-grouping="false" />
                </div>

                <div v-if="form.question_type !== 'essay'" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Đáp án</span>
                        <Button type="button" icon="pi pi-plus" label="Thêm" text @click="addAnswer" />
                    </div>
                    <div v-for="(answer, index) in form.answers" :key="index" class="grid items-center gap-2 md:grid-cols-[1fr_auto_auto]">
                        <InputText v-model="answer.answer_text" placeholder="Nội dung đáp án" />
                        <div class="flex items-center gap-2">
                            <Checkbox v-model="answer.is_correct" binary :input-id="`teacher-answer-${index}`" />
                            <label :for="`teacher-answer-${index}`">Đúng</label>
                        </div>
                        <Button type="button" icon="pi pi-times" v-tooltip.top="'Xóa đáp án'" text severity="danger" @click="removeAnswer(index)" />
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <Button type="button" label="Hủy" severity="secondary" text @click="dialogVisible = false" />
                    <Button type="submit" label="Lưu" :loading="saving" />
                </div>
            </form>
        </Dialog>
    </AppShell>
</template>
