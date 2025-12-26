@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Chỉnh sửa lớp học</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $class->name }}</p>
        </div>

        <form method="POST" action="{{ route('teacher.classes.update', $class) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6" onsubmit="return confirmUpdate(this, 'Bạn có chắc chắn muốn cập nhật lớp học này?');">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tên lớp <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $class->name) }}" required
                           class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Mã lớp <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="code" id="code" value="{{ old('code', $class->code) }}" required
                           class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Môn học <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject', $class->subject) }}" required
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
                        <input type="number" name="start_year" id="start_year" value="{{ old('start_year', $class->start_year) }}" required min="2000" max="2100"
                               class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">
                        @error('start_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Năm kết thúc <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="end_year" id="end_year" value="{{ old('end_year', $class->end_year) }}" required min="2000" max="2100"
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
                              class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 transition-colors duration-200">{{ old('description', $class->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $class->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        Lớp học đang hoạt động
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('teacher.classes.show', $class) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    Hủy
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Cập nhật
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('teacher.classes.destroy', $class) }}" class="mt-6 bg-red-50 dark:bg-red-900/20 rounded-lg shadow p-6"
              onsubmit="return confirmDelete(this, 'Bạn có chắc chắn muốn xóa lớp này? Tất cả dữ liệu liên quan sẽ bị xóa.');">
            @csrf
            @method('DELETE')
            <h3 class="text-lg font-medium text-red-900 dark:text-red-300 mb-2">Xóa lớp học</h3>
            <p class="text-sm text-red-700 dark:text-red-400 mb-4">
                Hành động này không thể hoàn tác. Tất cả học sinh sẽ bị xóa khỏi lớp.
            </p>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Xóa lớp học
            </button>
        </form>
    </div>
</div>
@endsection
