@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $class->name }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $class->subject }} - {{ $class->code }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.classes.edit', $class) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Chỉnh sửa
                </a>
                <a href="{{ route('admin.classes.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Quay lại
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- Thông tin lớp học -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Học sinh</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $class->students->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Giáo viên</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $class->teachers->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Bài tập</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $class->exams->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin chi tiết -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Thông tin chi tiết</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Năm học</p>
                    <p class="text-gray-900 dark:text-white">{{ $class->start_year }} - {{ $class->end_year }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Trạng thái</p>
                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $class->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                        {{ $class->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                    </span>
                </div>
                @if($class->description)
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Mô tả</p>
                        <p class="text-gray-900 dark:text-white">{{ $class->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Danh sách giáo viên -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Giáo viên</h2>
            @if($class->teachers->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">Chưa có giáo viên nào.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($class->teachers as $teacher)
                        <div class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <svg class="w-10 h-10 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $teacher->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $teacher->username }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Danh sách học sinh -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Học sinh</h2>
            @if($class->students->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">Chưa có học sinh nào.</p>
            @else
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tên</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Username</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($class->students as $student)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $student->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $student->username }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden space-y-3">
                    @foreach($class->students as $student)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $student->name }}</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">@{{ $student->username }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Danh sách bài tập -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Bài tập</h2>
            @if($class->exams->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">Chưa có bài tập nào.</p>
            @else
                <div class="space-y-3">
                    @foreach($class->exams as $exam)
                        <div class="flex justify-between items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ $exam->title }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Thời gian: {{ $exam->duration }} phút</p>
                            </div>
                            <a href="{{ route('admin.exams.show', $exam) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                Xem chi tiết →
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
