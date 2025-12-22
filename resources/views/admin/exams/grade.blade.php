@extends('layouts.app')

@section('title', 'Chấm bài thi')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Chấm bài thi</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Học viên: <strong>{{ $userExam->user->name }}</strong> - 
                Đề thi: <strong>{{ $userExam->exam->title }}</strong>
            </p>
        </div>
        <a href="{{ route('admin.grading.exam.users', $userExam->exam_id) }}" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            ← Quay lại danh sách
        </a>
    </div>
</div>

<form action="{{ route('admin.grading.submit', $userExam) }}" method="POST">
    @csrf
    
    <div class="space-y-6">
        @foreach($userExam->userAnswers as $index => $userAnswer)
            @if($userAnswer->question->question_type === 'essay')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Câu hỏi {{ $index + 1 }}
                        </h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ $userAnswer->question->question_text }}</p>
                        
                        @if($userAnswer->question->image_url)
                            <div class="mt-3">
                                <img src="{{ minio_image_base64($userAnswer->question->image_url) }}" 
                                     alt="Hình ảnh câu hỏi" 
                                     class="max-w-full h-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            </div>
                        @endif
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Câu trả lời của học viên:</p>
                        <div class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $userAnswer->essay_answer ?? 'Không có câu trả lời' }}</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="score_{{ $userAnswer->question_id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Điểm (0-{{ $userAnswer->question->points }})
                            </label>
                            <input type="number" 
                                   name="grades[{{ $userAnswer->question_id }}][score]" 
                                   id="score_{{ $userAnswer->question_id }}"
                                   min="0" 
                                   max="{{ $userAnswer->question->points }}" 
                                   step="0.5"
                                   value="{{ old('grades.'.$userAnswer->question_id.'.score', $userAnswer->essay_score) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="feedback_{{ $userAnswer->question_id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nhận xét của giáo viên
                            </label>
                            <textarea name="grades[{{ $userAnswer->question_id }}][feedback]" 
                                      id="feedback_{{ $userAnswer->question_id }}"
                                      rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Nhập nhận xét cho học viên..."
                            >{{ old('grades.'.$userAnswer->question_id.'.feedback', $userAnswer->admin_feedback) }}</textarea>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.grading.exam.users', $userExam->exam_id) }}" 
                    class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-base font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Hủy
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Lưu kết quả chấm
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
