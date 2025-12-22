@extends('layouts.app')

@section('title', 'Lịch sử làm bài')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Lịch sử làm bài</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            Đề thi: <span class="font-semibold">{{ $exam->title }}</span> - 
            Người dùng: <span class="font-semibold">{{ $user->name }} ({{ $user->username }})</span>
        </p>
    </div>

    @if($history->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Lần</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Điểm</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Thời gian bắt đầu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Thời gian hoàn thành</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Thời gian làm</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($history as $index => $userExam)
                        @php
                            $duration = null;
                            $durationMinutes = 0;
                            $durationSeconds = 0;
                            if ($userExam->started_at && $userExam->completed_at) {
                                $totalSeconds = $userExam->started_at->diffInSeconds($userExam->completed_at);
                                $durationMinutes = floor($totalSeconds / 60);
                                $durationSeconds = $totalSeconds % 60;
                                $duration = true;
                            }
                        @endphp
                        <tr class="{{ $index === 0 ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $history->count() - $index }}
                                @if($index === 0)
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                        Mới nhất
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($userExam->status === 'completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        Hoàn thành
                                    </span>
                                @elseif($userExam->status === 'in_progress')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                        Đang làm
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Chưa bắt đầu
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($userExam->score !== null)
                                    <span class="font-semibold {{ $userExam->score >= $exam->min_score ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $userExam->score }}
                                    </span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $userExam->started_at ? $userExam->started_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $userExam->completed_at ? $userExam->completed_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                @if($duration !== null)
                                    <span class="font-medium">{{ $durationMinutes }} phút {{ $durationSeconds }} giây</span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tổng số lần làm</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $history->where('status', 'completed')->count() }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Điểm cao nhất</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ $history->where('status', 'completed')->max('score') ?? '-' }}
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Điểm trung bình</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $history->where('status', 'completed')->avg('score') ? number_format($history->where('status', 'completed')->avg('score'), 2) : '-' }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400">Chưa có lịch sử làm bài.</p>
    @endif

    <div class="mt-6">
        <a href="{{ route('admin.exams.show', $exam) }}" 
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-gray-100 font-bold py-2 px-4 rounded">
            Quay lại
        </a>
    </div>
</div>
@endsection
