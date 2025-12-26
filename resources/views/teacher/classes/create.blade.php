@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tạo lớp học mới</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Tạo lớp học và thêm học sinh vào lớp</p>
        </div>

        <form method="POST" action="{{ route('teacher.classes.store') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6" onsubmit="return confirmUpdate(this, 'Bạn có chắc chắn muốn tạo lớp học mới?');">
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
                        <input type="number" name="start_year" id="start_year" value="{{ old('start_year', date('Y')) }}" required min="2000" max="2100"
                               class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">
                        @error('start_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Năm kết thúc <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="end_year" id="end_year" value="{{ old('end_year', date('Y') + 1) }}" required min="2000" max="2100"
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Thêm học sinh vào lớp
                    </label>
                    <div class="border border-gray-300 dark:border-gray-600 rounded-md p-4 max-h-64 overflow-y-auto">
                        @if($students->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">Không có học sinh nào chưa được phân lớp.</p>
                        @else
                            @foreach($students as $student)
                                <label class="flex items-center py-2 hover:bg-gray-50 dark:hover:bg-gray-700 px-2 rounded">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" 
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ $student->name }}</span>
                                </label>
                            @endforeach
                        @endif
                    </div>
                    @error('student_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('teacher.classes.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
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
