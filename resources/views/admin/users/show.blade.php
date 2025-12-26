@extends('layouts.app')

@section('title', 'Chi tiết người dùng')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $user->email }}</p>
        </div>
        <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Sửa
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Ngày tạo</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $user->created_at->format('d/m/Y') }}</p>
        </div>
        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Số bài thi</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $user->userExams->count() }}</p>
        </div>
        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Đã hoàn thành</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $user->userExams->where('status', 'completed')->count() }}</p>
        </div>
        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Điểm TB</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                @if($user->userExams->where('status', 'completed')->count() > 0)
                    {{ round($user->userExams->where('status', 'completed')->avg('score'), 1) }}%
                @else
                    N/A
                @endif
            </p>
        </div>
    </div>

    <a href="{{ route('admin.users.index') }}" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 font-bold py-2 px-4 rounded">
        Quay lại
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Lịch sử thi</h3>
    
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bài tập</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ngày thi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Điểm</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kết quả</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($user->userExams->where('status', 'completed') as $userExam)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $userExam->exam->title }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $userExam->completed_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $userExam->score }}%</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($userExam->score >= $userExam->exam->min_score)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                Đạt
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                Không đạt
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Chưa có lịch sử thi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
