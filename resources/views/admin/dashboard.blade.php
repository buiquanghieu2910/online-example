@extends('layouts.app')

@section('title', 'Bảng điều khiển')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Tổng số bài tập</dt>
                    <dd class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalExams }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Tổng người dùng</dt>
                    <dd class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalUsers }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Tổng lượt thi</dt>
                    <dd class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalAttempts }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Bài tập gần đây</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($recentExams as $exam)
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $exam->title }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $exam->duration }} phút</p>
                        </div>
                        <a href="{{ route('admin.exams.show', $exam) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">Xem</a>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">Chưa có bài tập nào.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Kết quả gần đây</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($recentResults as $result)
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $result->user->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $result->exam->title }}</p>
                        </div>
                        <span class="px-2 py-1 text-sm rounded {{ $result->score >= $result->exam->pass_score ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                            {{ $result->score }}%
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">Chưa có kết quả nào.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
