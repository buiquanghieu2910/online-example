@extends('layouts.app')

@section('title', 'Kết quả của tôi')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Kết quả của tôi</h2>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Xem lại các bài thi đã hoàn thành và điểm số</p>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-6">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Đề thi</th>
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
                            <a href="{{ route('user.results.show', $result) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
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
@endsection
