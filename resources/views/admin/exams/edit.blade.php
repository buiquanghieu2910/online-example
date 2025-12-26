@extends('layouts.app')

@section('title', 'Chỉnh sửa bài tập')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Chỉnh sửa bài tập</h2>

    <form action="{{ route('admin.exams.update', $exam) }}" method="POST" onsubmit="return confirmUpdate(this, 'Bạn có chắc chắn muốn cập nhật bài tập này?');">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tiêu đề</label>
            <input type="text" name="title" id="title" value="{{ old('title', $exam->title) }}" required
                class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 transition-colors duration-200 @error('title') border-red-500 @enderror">
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mô tả</label>
            <textarea name="description" id="description" rows="4"
                class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 transition-colors duration-200 @error('description') border-red-500 @enderror">{{ old('description', $exam->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thời gian (phút)</label>
                <input type="number" name="duration" id="duration" value="{{ old('duration', $exam->duration) }}" required
                    class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 transition-colors duration-200 @error('duration') border-red-500 @enderror">
                @error('duration')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="min_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Điểm tối thiểu để đạt</label>
                <input type="number" name="min_score" id="min_score" value="{{ old('min_score', $exam->min_score) }}" required min="0" step="0.5"
                    class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 transition-colors duration-200 @error('min_score') border-red-500 @enderror">
                @error('min_score')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="start_time" class="block text-sm font-medium text-gray-700">Thời gian bắt đầu (Tùy chọn)</label>
                <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time', $exam->start_time?->format('Y-m-d\TH:i')) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="end_time" class="block text-sm font-medium text-gray-700">Thời gian kết thúc (Tùy chọn)</label>
                <input type="datetime-local" name="end_time" id="end_time" value="{{ old('end_time', $exam->end_time?->format('Y-m-d\TH:i')) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $exam->is_active) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Kích hoạt</span>
            </label>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.exams.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                Hủy
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Cập nhật bài tập
            </button>
        </div>
    </form>
</div>
@endsection
