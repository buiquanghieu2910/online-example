@extends('layouts.app')

@section('title', 'Thông tin cá nhân')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    @if(session('success'))
        <div class="p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- Update Profile Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow transition-colors">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Thông tin cá nhân</h2>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tên <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}"
                           class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 transition-colors duration-200 @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : (auth()->user()->role === 'teacher' ? route('teacher.dashboard') : route('student.exams.index')) }}" 
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Hủy
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow transition-colors">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Đổi mật khẩu</h2>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Mật khẩu hiện tại <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 transition-colors duration-200 @error('current_password') border-red-500 @enderror"
                           required>
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Mật khẩu mới <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 transition-colors duration-200 @error('password') border-red-500 @enderror"
                           required>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Mật khẩu phải có ít nhất 8 ký tự.
                    </p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Xác nhận mật khẩu mới <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           class="mt-1 block w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 transition-colors duration-200"
                           required>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : (auth()->user()->role === 'teacher' ? route('teacher.dashboard') : route('student.exams.index')) }}" 
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Hủy
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Đổi mật khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow transition-colors">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Thông tin tài khoản</h2>
        </div>

        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tên:</span>
                <span class="text-sm text-gray-900 dark:text-white">{{ $user->name }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Username:</span>
                <span class="text-sm text-gray-900 dark:text-white">{{ $user->username }}</span>
            </div>
            <div class="flex justify-between items-center py-3">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Vai trò:</span>
                <span class="text-sm">
                    @if($user->role === 'admin')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100">Admin</span>
                    @elseif($user->role === 'teacher')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">Giáo viên</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">Học sinh</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
