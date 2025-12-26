@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Quản lý lớp học</h1>
        <a href="{{ route('admin.classes.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tạo lớp mới
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($classes->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Chưa có lớp học</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Bắt đầu bằng cách tạo lớp học đầu tiên.</p>
            <div class="mt-6">
                <a href="{{ route('admin.classes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tạo lớp mới
                </a>
            </div>
        </div>
    @else
        <!-- Desktop Table View -->
        <div class="hidden md:block bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Tên lớp
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Mã lớp
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Môn học
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Năm học
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Giáo viên
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Học sinh
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Bài tập
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Trạng thái
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Thao tác
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($classes as $class)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $class->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $class->code }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $class->subject }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $class->start_year }} - {{ $class->end_year }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $class->teachers_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ $class->students_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        {{ $class->exams_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                        {{ $class->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.classes.show', $class) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                        Xem
                                    </a>
                                    <a href="{{ route('admin.classes.edit', $class) }}" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 mr-3">
                                        Sửa
                                    </a>
                                    <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" class="inline" onsubmit="return confirmDelete(this, 'Bạn có chắc chắn muốn xóa lớp học này? Tất cả dữ liệu liên quan sẽ bị xóa.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            @foreach($classes as $class)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $class->name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $class->subject }} - {{ $class->code }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded {{ $class->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                            {{ $class->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                        </span>
                    </div>
                    
                    <div class="space-y-2 mb-3">
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $class->start_year }} - {{ $class->end_year }}
                        </div>
                        <div class="flex gap-4 text-sm">
                            <span class="inline-flex items-center">
                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-1"></span>
                                {{ $class->teachers_count }} GV
                            </span>
                            <span class="inline-flex items-center">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                                {{ $class->students_count }} HS
                            </span>
                            <span class="inline-flex items-center">
                                <span class="w-2 h-2 bg-purple-500 rounded-full mr-1"></span>
                                {{ $class->exams_count }} BT
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('admin.classes.show', $class) }}" class="flex-1 px-3 py-2 text-center text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                            Xem
                        </a>
                        <a href="{{ route('admin.classes.edit', $class) }}" class="flex-1 px-3 py-2 text-center text-sm bg-yellow-600 text-white rounded hover:bg-yellow-700">
                            Sửa
                        </a>
                        <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" class="flex-1" onsubmit="return confirmDelete(this, 'Bạn có chắc chắn muốn xóa lớp học này? Tất cả dữ liệu liên quan sẽ bị xóa.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-3 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                Xóa
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $classes->links() }}
        </div>
    @endif
</div>
@endsection
