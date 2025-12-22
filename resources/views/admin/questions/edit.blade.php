@extends('layouts.app')

@section('title', 'Chỉnh sửa câu hỏi')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Chỉnh sửa câu hỏi cho: {{ $exam->title }}</h2>

    <form action="{{ route('admin.questions.update', [$exam, $question]) }}" method="POST" id="questionForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="question_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Câu hỏi</label>
            <textarea name="question_text" id="question_text" rows="3" required
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('question_text') border-red-500 @enderror">{{ old('question_text', $question->question_text) }}</textarea>
            @error('question_text')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hình ảnh (tùy chọn)</label>
            @if($question->image_url)
                <div class="mt-2 mb-2">
                    <img src="{{ minio_image_base64($question->image_url) }}" alt="Current image" class="max-w-xs rounded-md shadow">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Hình ảnh hiện tại</p>
                </div>
            @endif
            <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(event)"
                class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100
                    dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600">
            @error('image')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <div id="imagePreview" class="mt-2 hidden">
                <img id="preview" src="" alt="Preview" class="max-w-xs rounded-md shadow">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label for="question_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Loại câu hỏi</label>
                <select name="question_type" id="question_type" required onchange="updateAnswerFields()"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="multiple_choice" {{ old('question_type', $question->question_type) == 'multiple_choice' ? 'selected' : '' }}>Trắc nghiệm</option>
                    <option value="true_false" {{ old('question_type', $question->question_type) == 'true_false' ? 'selected' : '' }}>Đúng/Sai</option>
                    <option value="essay" {{ old('question_type', $question->question_type) == 'essay' ? 'selected' : '' }}>Tự luận</option>
                </select>
            </div>

            <div>
                <label for="points" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Điểm</label>
                <input type="number" name="points" id="points" value="{{ old('points', $question->points) }}" required min="1"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thứ tự</label>
                <input type="number" name="order" id="order" value="{{ old('order', $question->order) }}" required min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <div id="answersSection" class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Các câu trả lời</label>
            <div id="answersContainer">
                @foreach($question->answers as $index => $answer)
                    <div class="flex items-center mb-2 answer-row">
                        <input type="hidden" name="answers[{{ $index }}][id]" value="{{ $answer->id }}">
                        <input type="text" name="answers[{{ $index }}][answer_text]" value="{{ old("answers.$index.answer_text", $answer->answer_text) }}" placeholder="Nội dung câu trả lời" required
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label class="ml-3 flex items-center">
                            <input type="checkbox" name="answers[{{ $index }}][is_correct]" value="1" {{ old("answers.$index.is_correct", $answer->is_correct) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Đúng</span>
                        </label>
                        @if($index > 1)
                            <button type="button" onclick="removeAnswer(this)" class="ml-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Xóa</button>
                        @endif
                    </div>
                @endforeach
            </div>
            <button type="button" onclick="addAnswer()" id="addAnswerBtn"
                class="mt-2 bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                Thêm câu trả lời
            </button>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.questions.index', $exam) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-gray-100 font-bold py-2 px-4 rounded">
                Hủy
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Cập nhật câu hỏi
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let answerCount = {{ $question->answers->count() }};

function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}

function addAnswer() {
    const container = document.getElementById('answersContainer');
    const newRow = document.createElement('div');
    newRow.className = 'flex items-center mb-2 answer-row';
    newRow.innerHTML = `
        <input type="text" name="answers[${answerCount}][answer_text]" placeholder="Nội dung câu trả lời" required
            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        <label class="ml-3 flex items-center">
            <input type="checkbox" name="answers[${answerCount}][is_correct]" value="1"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Đúng</span>
        </label>
        <button type="button" onclick="removeAnswer(this)" class="ml-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Xóa</button>
    `;
    container.appendChild(newRow);
    answerCount++;
}

function removeAnswer(button) {
    button.closest('.answer-row').remove();
}

function updateAnswerFields() {
    const questionType = document.getElementById('question_type').value;
    const answersSection = document.getElementById('answersSection');
    
    if (questionType === 'essay') {
        answersSection.style.display = 'none';
    } else {
        answersSection.style.display = 'block';
        document.getElementById('addAnswerBtn').style.display = questionType === 'true_false' ? 'none' : 'inline-block';
    }
}

updateAnswerFields();
</script>
@endpush
@endsection
