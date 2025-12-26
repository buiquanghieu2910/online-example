@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tạo lớp học mới</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Tạo lớp học và gán giáo viên, học sinh</p>
        </div>

        <form method="POST" action="{{ route('admin.classes.store') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6" onsubmit="return confirmUpdate(this, 'Bạn có chắc chắn muốn tạo lớp học mới?');">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tên lớp <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Mã lớp <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required
                           class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Môn học <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                           class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">
                    @error('subject')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Năm bắt đầu <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="start_year" id="start_year" value="{{ old('start_year') }}" required min="2000" max="2100"
                               class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">
                        @error('start_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Năm kết thúc <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="end_year" id="end_year" value="{{ old('end_year') }}" required min="2000" max="2100"
                               class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">
                        @error('end_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Mô tả
                    </label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        Lớp học đang hoạt động
                    </label>
                </div>

                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Gán giáo viên <span class="text-red-500">*</span>
                    </label>
                    <select name="teacher_id" id="teacher_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">-- Chọn giáo viên --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }} ({{ $teacher->username }})
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Thêm học sinh vào lớp
                    </label>
                    <div class="border border-gray-300 dark:border-gray-600 rounded-md p-4 max-h-64 overflow-y-auto">
                        @forelse($students as $student)
                            <div class="flex items-center mb-2">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" id="student_{{ $student->id }}"
                                       {{ in_array($student->id, old('student_ids', [])) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="student_{{ $student->id }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $student->name }} ({{ $student->username }})
                                </label>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Không có học sinh nào.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.classes.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    Hủy
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Tạo lớp học
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        border-color: #d1d5db;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        color: #111827;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .dark .select2-container--default .select2-selection--single {
        background-color: #374151;
        border-color: #4b5563;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #fff;
    }
    .dark .select2-dropdown {
        background-color: #374151;
        border-color: #4b5563;
    }
    .dark .select2-results__option {
        color: #fff;
    }
    .dark .select2-results__option--highlighted {
        background-color: #4b5563;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#teacher_id').select2({
            placeholder: '-- Chọn giáo viên --',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush