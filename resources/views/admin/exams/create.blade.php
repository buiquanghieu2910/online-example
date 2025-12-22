@extends('layouts.app')

@section('title', 'Tạo đề thi')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Tạo đề thi mới</h2>

    <form action="{{ route('admin.exams.store') }}" method="POST" id="examForm" enctype="multipart/form-data">
        @csrf

        <!-- Thông tin đề thi -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Thông tin đề thi</h3>
            
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tiêu đề</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mô tả</label>
                <textarea name="description" id="description" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thời gian (phút)</label>
                    <input type="number" name="duration" id="duration" value="{{ old('duration', 60) }}" required min="1"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('duration') border-red-500 @enderror">
                    @error('duration')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="min_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Điểm tối thiểu để đạt</label>
                    <input type="number" name="min_score" id="min_score" value="{{ old('min_score', 5) }}" required min="0" step="0.5"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('min_score') border-red-500 @enderror">
                    @error('min_score')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thời gian bắt đầu (Tùy chọn)</label>
                    <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thời gian kết thúc (Tùy chọn)</label>
                    <input type="datetime-local" name="end_time" id="end_time" value="{{ old('end_time') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Kích hoạt</span>
                </label>
            </div>
        </div>

        <!-- Câu hỏi -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Câu hỏi</h3>
                <button type="button" onclick="addQuestion()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                    + Thêm câu hỏi
                </button>
            </div>

            <div id="questionsContainer" class="space-y-6">
                <!-- Questions will be added here dynamically -->
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('admin.exams.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-gray-100 font-bold py-2 px-4 rounded">
                Hủy
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Tạo đề thi
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let questionCount = 0;

function addQuestion() {
    questionCount++;
    const container = document.getElementById('questionsContainer');
    const questionHtml = `
        <div class="question-block border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-900" data-question="${questionCount}">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200">Câu hỏi ${questionCount}</h4>
                <button type="button" onclick="removeQuestion(${questionCount})" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Xóa
                </button>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nội dung câu hỏi</label>
                <textarea name="questions[${questionCount}][question_text]" rows="2" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hình ảnh (tùy chọn)</label>
                <input type="file" name="questions[${questionCount}][image]" accept="image/*" 
                    class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100
                        dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600">
            </div>

            <div class="grid grid-cols-3 gap-3 mb-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Loại câu hỏi</label>
                    <select name="questions[${questionCount}][question_type]" onchange="updateAnswers(${questionCount})" 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="multiple_choice">Trắc nghiệm</option>
                        <option value="true_false">Đúng/Sai</option>
                        <option value="essay">Tự luận</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Điểm</label>
                    <input type="number" name="questions[${questionCount}][points]" value="1" required min="0.5" step="0.5"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thứ tự</label>
                    <input type="number" name="questions[${questionCount}][order]" value="${questionCount}" required min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="answers-section-${questionCount}">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Các câu trả lời</label>
                <div class="answers-container-${questionCount} space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="text" name="questions[${questionCount}][answers][0][answer_text]" placeholder="Đáp án 1" required
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label class="flex items-center">
                            <input type="checkbox" name="questions[${questionCount}][answers][0][is_correct]" value="1"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Đúng</span>
                        </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" name="questions[${questionCount}][answers][1][answer_text]" placeholder="Đáp án 2" required
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label class="flex items-center">
                            <input type="checkbox" name="questions[${questionCount}][answers][1][is_correct]" value="1"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Đúng</span>
                        </label>
                    </div>
                </div>
                <button type="button" onclick="addAnswer(${questionCount})" class="add-answer-btn-${questionCount} mt-2 bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                    Thêm đáp án
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', questionHtml);
}

function removeQuestion(questionId) {
    const questionBlock = document.querySelector(`[data-question="${questionId}"]`);
    if (questionBlock) {
        questionBlock.remove();
    }
}

let answerCounts = {};

function addAnswer(questionId) {
    if (!answerCounts[questionId]) {
        answerCounts[questionId] = 2;
    }
    answerCounts[questionId]++;
    
    const container = document.querySelector(`.answers-container-${questionId}`);
    const answerIndex = answerCounts[questionId] - 1;
    const answerHtml = `
        <div class="flex items-center gap-2">
            <input type="text" name="questions[${questionId}][answers][${answerIndex}][answer_text]" placeholder="Đáp án ${answerIndex + 1}" required
                class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <label class="flex items-center">
                <input type="checkbox" name="questions[${questionId}][answers][${answerIndex}][is_correct]" value="1"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Đúng</span>
            </label>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', answerHtml);
}

function updateAnswers(questionId) {
    const select = document.querySelector(`[data-question="${questionId}"] select[name*="question_type"]`);
    const answersSection = document.querySelector(`.answers-section-${questionId}`);
    const answersContainer = document.querySelector(`.answers-container-${questionId}`);
    const addBtn = document.querySelector(`.add-answer-btn-${questionId}`);
    
    if (select.value === 'essay') {
        answersSection.style.display = 'none';
        answersContainer.querySelectorAll('input').forEach(input => {
            input.disabled = true;
            input.removeAttribute('required');
        });
    } else {
        answersSection.style.display = 'block';
        answersContainer.querySelectorAll('input').forEach(input => {
            input.disabled = false;
        });
        answersContainer.querySelectorAll('input[type="text"]').forEach(input => {
            input.setAttribute('required', 'required');
        });
        
        if (select.value === 'true_false') {
            answersContainer.innerHTML = `
                <div class="flex items-center gap-2">
                    <input type="text" name="questions[${questionId}][answers][0][answer_text]" value="Đúng" required readonly
                        class="flex-1 rounded-md border-gray-300 dark:border-gray-600 shadow-sm bg-gray-100 dark:bg-gray-900 dark:text-gray-100">
                    <label class="flex items-center">
                        <input type="checkbox" name="questions[${questionId}][answers][0][is_correct]" value="1"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Đáp án đúng</span>
                    </label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" name="questions[${questionId}][answers][1][answer_text]" value="Sai" required readonly
                        class="flex-1 rounded-md border-gray-300 dark:border-gray-600 shadow-sm bg-gray-100 dark:bg-gray-900 dark:text-gray-100">
                    <label class="flex items-center">
                        <input type="checkbox" name="questions[${questionId}][answers][1][is_correct]" value="1"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Đáp án đúng</span>
                    </label>
                </div>
            `;
            addBtn.style.display = 'none';
        } else {
            addBtn.style.display = 'inline-block';
        }
    }
}

// Add first question on page load
document.addEventListener('DOMContentLoaded', function() {
    addQuestion();
});
</script>
@endpush
@endsection
