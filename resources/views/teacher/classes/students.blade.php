@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Quản lý học sinh</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $class->name }} - {{ $class->subject }}</p>
            </div>
            <a href="{{ route('teacher.classes.show', $class) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Quay lại
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Current Students -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Học sinh trong lớp ({{ $class->students->count() }})
                </h2>
                @if($class->students->isNotEmpty())
                    <button type="button" onclick="deleteSelectedFromList()" id="deleteListButton" class="hidden px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Xóa đã chọn
                    </button>
                @endif
            </div>
            
            @if($class->students->isEmpty())
                <p class="text-gray-600 dark:text-gray-400">Chưa có học sinh nào trong lớp.</p>
            @else
                <form id="deleteListForm" method="POST" action="{{ route('teacher.classes.students.removeMultiple', $class) }}">
                    @csrf
                    @method('DELETE')
                    <div class="mb-3">
                        <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <input type="checkbox" id="selectAllList" onchange="toggleAllList(this)"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                            Chọn tất cả
                        </label>
                    </div>
                    <div class="space-y-2">
                        @foreach($class->students as $student)
                            <label class="flex items-start p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" onchange="updateDeleteListButton()"
                                       class="delete-checkbox mt-1 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <div class="ml-3 flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $student->name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $student->username }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </form>

                <script>
                function toggleAllList(source) {
                    const checkboxes = document.querySelectorAll('.delete-checkbox');
                    checkboxes.forEach(checkbox => checkbox.checked = source.checked);
                    updateDeleteListButton();
                }

                function updateDeleteListButton() {
                    const checkboxes = document.querySelectorAll('.delete-checkbox:checked');
                    const deleteButton = document.getElementById('deleteListButton');
                    if (checkboxes.length > 0) {
                        deleteButton.classList.remove('hidden');
                        deleteButton.textContent = `Xóa ${checkboxes.length} học sinh`;
                    } else {
                        deleteButton.classList.add('hidden');
                    }
                }

                function deleteSelectedFromList() {
                    const checkboxes = document.querySelectorAll('.delete-checkbox:checked');
                    if (checkboxes.length === 0) {
                        alert('Vui lòng chọn ít nhất một học sinh để xóa.');
                        return;
                    }
                    if (confirm(`Bạn có chắc chắn muốn xóa ${checkboxes.length} học sinh khỏi lớp?`)) {
                        document.getElementById('deleteListForm').submit();
                    }
                }
                </script>
            @endif
        </div>

        <!-- Available Students -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                Thêm học sinh vào lớp ({{ $availableStudents->count() }})
            </h2>
            
            @if($availableStudents->isEmpty())
                <p class="text-gray-600 dark:text-gray-400">Tất cả học sinh đã được thêm vào lớp này.</p>
            @else
                <form method="POST" action="{{ route('teacher.classes.students.addMultiple', $class) }}">
                    @csrf
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Chọn học sinh</label>
                            <button type="button" onclick="toggleAllAdd(this)" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                Chọn tất cả
                            </button>
                        </div>
                        <div class="max-h-96 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-lg p-3">
                            <div class="space-y-2">
                                @foreach($availableStudents as $student)
                                    <label class="flex items-start p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" onchange="updateAddButton()"
                                               class="add-checkbox mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <div class="ml-3 flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $student->name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $student->username }}</p>
                                            @if($student->schoolClasses->isNotEmpty())
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @foreach($student->schoolClasses as $sc)
                                                        <span class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">{{ $sc->name }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('student_ids')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" id="addButton" disabled class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Thêm học sinh đã chọn
                    </button>
                </form>

                <script>
                function toggleAllAdd(button) {
                    const checkboxes = document.querySelectorAll('.add-checkbox');
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(checkbox => checkbox.checked = !allChecked);
                    button.textContent = allChecked ? 'Chọn tất cả' : 'Bỏ chọn tất cả';
                    updateAddButton();
                }

                function updateAddButton() {
                    const checkboxes = document.querySelectorAll('.add-checkbox:checked');
                    const addButton = document.getElementById('addButton');
                    if (checkboxes.length > 0) {
                        addButton.disabled = false;
                        addButton.textContent = `Thêm ${checkboxes.length} học sinh vào lớp`;
                    } else {
                        addButton.disabled = true;
                        addButton.textContent = 'Thêm học sinh đã chọn';
                    }
                }
                </script>
            @endif
        </div>
    </div>
</div>
@endsection
