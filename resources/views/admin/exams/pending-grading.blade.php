@extends('layouts.app')

@section('title', 'Chấm bài thi')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Chấm bài thi</h2>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Chọn bài tập để xem danh sách học viên cần chấm điểm</p>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-6">
        @if($exams->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Chưa có bài tập nào.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($exams as $exam)
                    @php
                        $pendingCount = \App\Models\UserExam::where('exam_id', $exam->id)
                            ->where('status', 'completed')
                            ->where('grading_status', 'pending_review')
                            ->count();
                    @endphp
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $exam->title }}
                            </h3>
                            @if($pendingCount > 0)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                    {{ $pendingCount }}
                                </span>
                            @endif
                        </div>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                            {{ $exam->description ?? 'Không có mô tả' }}
                        </p>
                        
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $exam->questions->count() }} câu hỏi
                            </div>
                            <a href="{{ route('admin.grading.exam.users', $exam) }}" 
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Xem danh sách
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
