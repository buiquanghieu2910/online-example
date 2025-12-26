@extends('layouts.app')

@section('title', 'Kết quả của tôi')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Kết quả của tôi</h2>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Xem lại các bài thi đã hoàn thành và điểm số</p>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <!-- Desktop Table -->
    <div class="hidden md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bài tập</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ngày thi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Điểm</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kết quả</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($results as $result)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $result->exam->title }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $result->completed_at->format('d/m/Y H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $result->score }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($result->score >= $result->exam->min_score)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        Đạt
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                        Không đạt
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('student.results.show', $result) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    Xem chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                Chưa có kết quả nào. Tham gia thi để xem kết quả tại đây.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-4 p-4">
        @forelse($results as $result)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                <div class="mb-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $result->exam->title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $result->completed_at->format('d/m/Y H:i:s') }}</p>
                </div>

                <div class="flex items-center justify-between mb-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Điểm:</span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-white ml-2">{{ $result->score }}</span>
                    </div>
                    @if($result->score >= $result->exam->min_score)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                            Đạt
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                            Không đạt
                        </span>
                    @endif
                </div>

                <a href="{{ route('student.results.show', $result) }}" class="flex items-center justify-center w-full text-center bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Xem chi tiết
                </a>
            </div>
        @empty
            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                Chưa có kết quả nào. Tham gia thi để xem kết quả tại đây.
            </div>
        @endforelse
    </div>
</div>
@endsection
