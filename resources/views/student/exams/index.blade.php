@extends('layouts.app')

@section('title', 'Bài tập khả dụng')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Bài tập khả dụng</h2>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Chọn một bài tập để bắt đầu kiểm tra kiến thức của bạn</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($exams as $exam)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="p-6">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 flex-1">{{ $exam->title }}</h3>
                    @if($exam->latestAttempt)
                        @if($exam->latestAttempt->score >= $exam->pass_score)
                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                Đạt
                            </span>
                        @else
                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                Không đạt
                            </span>
                        @endif
                    @else
                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            Chưa làm
                        </span>
                    @endif
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ Str::limit($exam->description, 100) }}</p>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Thời gian: {{ $exam->duration }} phút
                    </div>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Số câu hỏi: {{ $exam->questions_count }} câu
                    </div>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Điểm cần đạt: {{ $exam->min_score }}
                    </div>
                    @if($exam->latestAttempt)
                        <div class="flex items-center text-sm font-semibold {{ $exam->latestAttempt->score >= $exam->pass_score ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                            Điểm của bạn: {{ $exam->latestAttempt->score }}/10
                        </div>
                    @endif
                </div>

                @if($exam->latestAttempt)
                    <a href="{{ route('student.results.show', $exam->latestAttempt) }}" 
                       class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
                        Xem kết quả
                    </a>
                @else
                    <a href="{{ route('student.exams.show', $exam) }}" 
                       class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                        Bắt đầu làm bài
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Hiện tại không có bài tập nào.</p>
        </div>
    @endforelse
</div>
@endsection

