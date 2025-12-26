@extends('layouts.app')

@section('title', 'Quản lý câu hỏi')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Các câu hỏi cho: {{ $exam->title }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Tổng số: {{ $questions->count() }} câu hỏi</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.questions.create', $exam) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Thêm câu hỏi
                </a>
                <a href="{{ route('admin.exams.show', $exam) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-gray-100 font-bold py-2 px-4 rounded">
                    Quay lại bài tập
                </a>
            </div>
        </div>
    </div>

    <div class="p-6">
        @forelse($questions as $question)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Loại: {{ ucfirst(str_replace('_', ' ', $question->question_type)) }} | 
                            Điểm: {{ $question->points }} | 
                            Thứ tự: {{ $question->order }}
                        </p>
                        @if($question->image_url)
                            <div class="mt-2">
                                <img src="{{ minio_image_base64($question->image_url) }}" alt="Question image" class="max-w-md rounded-md shadow">
                            </div>
                        @endif
                    </div>
                    <div class="flex space-x-2 ml-4">
                        <a href="{{ route('admin.questions.edit', [$exam, $question]) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Sửa</a>
                        <form action="{{ route('admin.questions.destroy', [$exam, $question]) }}" method="POST" class="inline" onsubmit="return confirmDelete(this, 'Bạn có chắc chắn muốn xóa câu hỏi này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Xóa</button>
                        </form>
                    </div>
                </div>

                @if($question->question_type !== 'essay')
                    <div class="ml-6">
                        @foreach($question->answers as $answer)
                            <div class="flex items-center mb-2">
                                @if($answer->is_correct)
                                    <span class="text-green-600 dark:text-green-400 mr-2 font-bold">✓</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 mr-2">○</span>
                                @endif
                                <span class="{{ $answer->is_correct ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $answer->answer_text }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="text-center text-gray-500 dark:text-gray-400 py-8">Chưa có câu hỏi nào. Nhấn "Thêm câu hỏi" để tạo mới.</p>
        @endforelse
    </div>
</div>
@endsection
