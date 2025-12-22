@extends('layouts.app')

@section('title', 'Lịch sử làm bài')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Lịch sử làm bài</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $exam->title }}</p>
        </div>
        <a href="{{ route('user.results.index') }}" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            Quay lại
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổng số lần làm</div>
        <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
            {{ $completedHistory->count() }}
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Điểm cao nhất</div>
        <div class="mt-2 text-3xl font-semibold text-green-600 dark:text-green-400">
            {{ $completedHistory->max('score') ?? 0 }}/10
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Điểm trung bình</div>
        <div class="mt-2 text-3xl font-semibold text-blue-600 dark:text-blue-400">
            {{ $completedHistory->avg('score') ? number_format($completedHistory->avg('score'), 1) : 0 }}/10
        </div>
    </div>
</div>

<!-- History Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Danh sách các lần làm</h3>
        
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Lần</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Điểm</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ngày bắt đầu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ngày hoàn thành</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Thời gian làm</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($history as $index => $attempt)
                    <tr class="{{ $index === 0 ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $history->count() - $index }}
                                @if($index === 0)
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                        Mới nhất
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->status === 'completed')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                    Đã hoàn thành
                                </span>
                            @elseif($attempt->status === 'in_progress')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                    Đang làm
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Chưa làm
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->status === 'completed')
                                <div class="text-sm font-semibold {{ $attempt->score >= $exam->min_score ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $attempt->score }}
                                    @if($attempt->score >= $exam->min_score)
                                        (Đạt)
                                    @else
                                        (Không đạt)
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $attempt->started_at ? $attempt->started_at->format('d/m/Y H:i:s') : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $attempt->completed_at ? $attempt->completed_at->format('d/m/Y H:i:s') : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->started_at && $attempt->completed_at)
                                @php
                                    $duration = $attempt->started_at->diffInMinutes($attempt->completed_at);
                                    $hours = floor($duration / 60);
                                    $minutes = $duration % 60;
                                @endphp
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($hours > 0)
                                        {{ $hours }} giờ
                                    @endif
                                    {{ $minutes }} phút
                                </div>
                            @else
                                <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($attempt->status === 'completed')
                                <a href="{{ route('user.results.show', $attempt) }}" 
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    Xem chi tiết
                                </a>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Chưa có lịch sử làm bài
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
