@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Thống kê điểm danh</h1>
        <a href="{{ route('teacher.attendances.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Quay lại điểm danh
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('teacher.attendances.statistics') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Lớp học
                </label>
                <select id="class_id" 
                        name="class_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        onchange="this.form.submit()">
                    <option value="">Tất cả lớp</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} - {{ $class->subject }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Chọn học sinh
                </label>
                <select id="student_id" 
                        name="student_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        onchange="this.form.submit()">
                    <option value="">-- Chọn học sinh --</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                            @if($s->schoolClasses->isNotEmpty())
                                ({{ $s->schoolClasses->pluck('name')->join(', ') }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Tháng
                </label>
                <input type="month" 
                       id="month" 
                       name="month" 
                       value="{{ $month }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                       onchange="this.form.submit()">
            </div>
        </form>
    </div>

    @isset($student)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                Học sinh: {{ $student->name }}
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-green-100 dark:bg-green-900 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['present'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Có mặt</div>
                </div>
                <div class="bg-red-100 dark:bg-red-900 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['absent'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Vắng</div>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['late'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Muộn</div>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['excused'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Có phép</div>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold text-gray-600 dark:text-gray-400">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Tổng số buổi</div>
                </div>
            </div>

            @if($attendances->isNotEmpty())
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Chi tiết điểm danh</h3>
                
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Ngày
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Ghi chú
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Giáo viên
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($attendances as $attendance)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $attendance->date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($attendance->status === 'present')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 rounded">Có mặt</span>
                                        @elseif($attendance->status === 'absent')
                                            <span class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 rounded">Vắng</span>
                                        @elseif($attendance->status === 'late')
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 rounded">Muộn</span>
                                        @else
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 rounded">Có phép</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $attendance->notes ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $attendance->teacher->name }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden space-y-3">
                    @foreach($attendances as $attendance)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $attendance->date->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $attendance->teacher->name }}</p>
                                </div>
                                @if($attendance->status === 'present')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 rounded text-xs font-medium">Có mặt</span>
                                @elseif($attendance->status === 'absent')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 rounded text-xs font-medium">Vắng</span>
                                @elseif($attendance->status === 'late')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 rounded text-xs font-medium">Muộn</span>
                                @else
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 rounded text-xs font-medium">Có phép</span>
                                @endif
                            </div>
                            @if($attendance->notes)
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                                    <span class="font-medium">Ghi chú:</span> {{ $attendance->notes }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600 dark:text-gray-400">Chưa có dữ liệu điểm danh cho tháng này.</p>
            @endif
        </div>
    @endisset
</div>
@endsection
