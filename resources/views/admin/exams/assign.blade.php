@extends('layouts.app')

@section('title', 'Gán người dùng cho đề thi')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Gán người dùng cho: {{ $exam->title }}</h2>

    <form action="{{ route('admin.exams.assign.store', $exam) }}" method="POST">
        @csrf

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Chọn người dùng</label>
            
            <div class="space-y-2 max-h-96 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-4">
                @forelse($allUsers as $user)
                    <label class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer">
                        <input type="checkbox" 
                            name="user_ids[]" 
                            value="{{ $user->id }}"
                            {{ in_array($user->id, $assignedUserIds) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-3 text-sm text-gray-900 dark:text-gray-100">
                            {{ $user->name }} ({{ $user->username }})
                        </span>
                    </label>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">Không có người dùng nào.</p>
                @endforelse
            </div>
            
            @error('user_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.exams.show', $exam) }}" 
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-gray-100 font-bold py-2 px-4 rounded">
                Hủy
            </a>
            <button type="submit" 
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Lưu
            </button>
        </div>
    </form>
</div>
@endsection
