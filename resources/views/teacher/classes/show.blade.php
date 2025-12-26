@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $class->name }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $class->subject }} • Năm học {{ $class->start_year }}-{{ $class->end_year }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('teacher.classes.edit', $class) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Chỉnh sửa
                </a>
                <a href="{{ route('teacher.classes.students', $class) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Quản lý học sinh
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Học sinh</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $class->students->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Bài tập</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $class->exams->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Trạng thái</p>
                    <p class="text-lg font-bold {{ $class->is_active ? 'text-green-600' : 'text-gray-600' }}">
                        {{ $class->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($class->description)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Mô tả</h2>
            <p class="text-gray-600 dark:text-gray-400">{{ $class->description }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Học sinh trong lớp ({{ $class->students->count() }})</h2>
            @if($class->students->isNotEmpty())
                <button type="button" onclick="deleteSelectedStudents()" id="deleteButton" class="hidden px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Xóa đã chọn
                </button>
            @endif
        </div>

        @if($class->students->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Chưa có học sinh nào trong lớp.</p>
                <a href="{{ route('teacher.classes.students', $class) }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Thêm học sinh
                </a>
            </div>
        @else
            <form id="deleteForm" method="POST" action="{{ route('teacher.classes.students.removeMultiple', $class) }}">
                @csrf
                @method('DELETE')
                
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes(this)"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">STT</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tên học sinh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tên đăng nhập</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($class->students as $index => $student)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" onchange="updateDeleteButton()"
                                               class="student-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $student->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ $student->username }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden space-y-3">
                    @foreach($class->students as $index => $student)
                        <label class="flex items-start gap-3 bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" onchange="updateDeleteButton()"
                                   class="student-checkbox mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">#{{ $index + 1 }}</span>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $student->name }}</h3>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300">@{{ $student->username }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </form>

            <script>
            function toggleAllCheckboxes(source) {
                const checkboxes = document.querySelectorAll('.student-checkbox');
                checkboxes.forEach(checkbox => checkbox.checked = source.checked);
                updateDeleteButton();
            }

            function updateDeleteButton() {
                const checkboxes = document.querySelectorAll('.student-checkbox:checked');
                const deleteButton = document.getElementById('deleteButton');
                if (checkboxes.length > 0) {
                    deleteButton.classList.remove('hidden');
                    deleteButton.textContent = `Xóa ${checkboxes.length} học sinh`;
                } else {
                    deleteButton.classList.add('hidden');
                }
            }

            function deleteSelectedStudents() {
                const checkboxes = document.querySelectorAll('.student-checkbox:checked');
                if (checkboxes.length === 0) {
                    alert('Vui lòng chọn ít nhất một học sinh để xóa.');
                    return;
                }
                if (confirm(`Bạn có chắc chắn muốn xóa ${checkboxes.length} học sinh khỏi lớp?`)) {
                    document.getElementById('deleteForm').submit();
                }
            }
            </script>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Bài tập của lớp</h2>
            <a href="{{ route('teacher.exams.create', ['class_id' => $class->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tạo bài tập mới
            </a>
        </div>
        @if($class->exams->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Chưa có bài tập nào cho lớp này.</p>
                <a href="{{ route('teacher.exams.create', ['class_id' => $class->id]) }}" class="mt-4 inline-block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Tạo bài tập đầu tiên
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($class->exams as $exam)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $exam->title }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $exam->description }}</p>
                                <div class="mt-2 flex gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <span>Thời gian: {{ $exam->duration }} phút</span>
                                    <span>Điểm tối thiểu: {{ $exam->min_score }}</span>
                                </div>
                            </div>
                            <a href="{{ route('teacher.exams.show', $exam) }}" class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                                Chi tiết
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

