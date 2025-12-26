@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Điểm danh học sinh</h1>
        <a href="{{ route('teacher.attendances.statistics') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Xem thống kê
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('teacher.attendances.store') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label for="date" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Ngày điểm danh
                </label>
                <input type="date" 
                       id="date" 
                       name="date" 
                       value="{{ $date }}"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition duration-200 shadow-sm hover:border-gray-400 dark:hover:border-gray-500"
                       onchange="updateFilter()">
            </div>

            <div>
                <label for="class_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Lớp học
                </label>
                <select id="class_id" 
                        name="class_id"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition duration-200 shadow-sm hover:border-gray-400 dark:hover:border-gray-500 cursor-pointer"
                        onchange="updateFilter()">
                    <option value="">Tất cả lớp</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} - {{ $class->subject }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <script>
        function updateFilter() {
            const date = document.getElementById('date').value;
            const classId = document.getElementById('class_id').value;
            let url = '{{ route('teacher.attendances.index') }}?date=' + date;
            if (classId) {
                url += '&class_id=' + classId;
            }
            window.location.href = url;
        }
        </script>

        @if($students->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="mt-4 text-gray-600 dark:text-gray-400 text-lg">
                    @if(request('class_id'))
                        Lớp này chưa có học sinh nào.
                    @else
                        Bạn chưa có học sinh nào trong các lớp.
                    @endif
                </p>
            </div>
        @else
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Tên học sinh</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Lớp</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($students as $index => $student)
                            @php
                                $attendance = $student->attendances->first();
                                $currentStatus = $attendance ? $attendance->status : 'present';
                                $currentNotes = $attendance ? $attendance->notes : '';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $student->name }}
                                    <input type="hidden" name="attendances[{{ $index }}][student_id]" value="{{ $student->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    @if($student->schoolClasses->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($student->schoolClasses as $class)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    {{ $class->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Chưa có lớp</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <select name="attendances[{{ $index }}][status]" 
                                            class="min-w-[140px] px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition duration-200 shadow-sm hover:border-gray-400 dark:hover:border-gray-500 cursor-pointer font-medium">
                                        <option value="present" {{ $currentStatus === 'present' ? 'selected' : '' }}>✓ Có mặt</option>
                                        <option value="absent" {{ $currentStatus === 'absent' ? 'selected' : '' }}>✗ Vắng</option>
                                        <option value="late" {{ $currentStatus === 'late' ? 'selected' : '' }}>⏱ Muộn</option>
                                        <option value="excused" {{ $currentStatus === 'excused' ? 'selected' : '' }}>📝 Có phép</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <input type="text" 
                                           name="attendances[{{ $index }}][notes]" 
                                           value="{{ $currentNotes }}"
                                           placeholder="Ghi chú..."
                                           class="w-full min-w-[200px] px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition duration-200 shadow-sm hover:border-gray-400 dark:hover:border-gray-500">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4">
                @foreach($students as $index => $student)
                    @php
                        $attendance = $student->attendances->first();
                        $currentStatus = $attendance ? $attendance->status : 'present';
                        $currentNotes = $attendance ? $attendance->notes : '';
                    @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full font-bold text-sm">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $student->name }}</h3>
                                @if($student->schoolClasses->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($student->schoolClasses as $class)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $class->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <input type="hidden" name="attendances[{{ $index }}][student_id]" value="{{ $student->id }}">
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trạng thái</label>
                                <select name="attendances[{{ $index }}][status]" 
                                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition duration-200">
                                    <option value="present" {{ $currentStatus === 'present' ? 'selected' : '' }}>✓ Có mặt</option>
                                    <option value="absent" {{ $currentStatus === 'absent' ? 'selected' : '' }}>✗ Vắng</option>
                                    <option value="late" {{ $currentStatus === 'late' ? 'selected' : '' }}>⏱ Muộn</option>
                                    <option value="excused" {{ $currentStatus === 'excused' ? 'selected' : '' }}>📝 Có phép</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ghi chú</label>
                                <input type="text" 
                                       name="attendances[{{ $index }}][notes]" 
                                       value="{{ $currentNotes }}"
                                       placeholder="Ghi chú..."
                                       class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition duration-200">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="inline-flex items-center px-8 py-3.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Lưu điểm danh
                </button>
            </div>
        @endif
    </form>
</div>
@endsection
