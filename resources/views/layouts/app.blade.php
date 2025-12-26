<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hệ thống thi trực tuyến')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
    <!-- Navbar -->
    @auth
    <nav class="sticky top-0 z-50 bg-white dark:bg-gray-800 shadow-lg transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-100">
                            @if(auth()->user()->role === 'admin')
                                Quản trị viên
                            @elseif(auth()->user()->role === 'teacher')
                                Giáo viên
                            @else
                                Thi trực tuyến
                            @endif
                        </h1>
                    </div>
                    
                    @if(auth()->user()->role === 'admin')
                    <div class="hidden md:ml-6 md:flex md:space-x-4 lg:space-x-8">
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Bảng điều khiển
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Người dùng
                        </a>
                        <a href="{{ route('admin.classes.index') }}" class="{{ request()->routeIs('admin.classes.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Lớp học
                        </a>
                        <a href="{{ route('admin.exams.index') }}" class="{{ request()->routeIs('admin.exams.*', 'admin.questions.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Bài tập
                        </a>
                        <a href="{{ route('admin.grading.pending') }}" class="{{ request()->routeIs('admin.grading.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Chấm bài
                        </a>
                    </div>
                    @elseif(auth()->user()->role === 'teacher')
                    <div class="hidden md:ml-6 md:flex md:space-x-4 lg:space-x-8">
                        <a href="{{ route('teacher.dashboard') }}" class="{{ request()->routeIs('teacher.dashboard') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Bảng điều khiển
                        </a>
                        <a href="{{ route('teacher.classes.index') }}" class="{{ request()->routeIs('teacher.classes.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Lớp học
                        </a>
                        <a href="{{ route('teacher.students.index') }}" class="{{ request()->routeIs('teacher.students.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Học sinh
                        </a>
                        <a href="{{ route('teacher.exams.index') }}" class="{{ request()->routeIs('teacher.exams.*', 'teacher.questions.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Bài tập
                        </a>
                        <a href="{{ route('teacher.attendances.index') }}" class="{{ request()->routeIs('teacher.attendances.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Điểm danh
                        </a>
                    </div>
                    @else
                    <div class="hidden md:ml-6 md:flex md:space-x-4 lg:space-x-8">
                        <a href="{{ route('student.exams.index') }}" class="{{ request()->routeIs('student.exams.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Bài tập
                        </a>
                        <a href="{{ route('student.results.index') }}" class="{{ request()->routeIs('student.results.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Kết quả
                        </a>
                    </div>
                    @endif
                </div>
                
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200" aria-label="Toggle dark mode">
                        <svg id="theme-toggle-dark-icon" class="w-5 h-5 text-gray-800 dark:text-gray-200 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="w-5 h-5 text-gray-800 dark:text-gray-200 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    
                    <!-- User Menu Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none">
                            <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-lg shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-600">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Cập nhật thông tin
                                </span>
                            </a>
                            <button onclick="confirmLogout()" class="w-full text-left block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Đăng xuất
                                </span>
                            </button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="hidden" id="logout-form">
                        @csrf
                    </form>
                    
                    <!-- Mobile menu button -->
                    <button onclick="toggleMobileMenu()" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <svg class="h-6 w-6" id="mobile-menu-icon-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 w-6 hidden" id="mobile-menu-icon-close" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.dashboard') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Bảng điều khiển
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.users.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Người dùng
                        </a>
                        <a href="{{ route('admin.classes.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.classes.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Lớp học
                        </a>
                        <a href="{{ route('admin.exams.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.exams.*', 'admin.questions.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Bài tập
                        </a>
                        <a href="{{ route('admin.grading.pending') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.grading.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Chấm bài
                        </a>
                    @elseif(auth()->user()->role === 'teacher')
                        <a href="{{ route('teacher.dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('teacher.dashboard') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Bảng điều khiển
                        </a>
                        <a href="{{ route('teacher.classes.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('teacher.classes.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Lớp học
                        </a>
                        <a href="{{ route('teacher.students.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('teacher.students.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Học sinh
                        </a>
                        <a href="{{ route('teacher.exams.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('teacher.exams.*', 'teacher.questions.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Bài tập
                        </a>
                        <a href="{{ route('teacher.attendances.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('teacher.attendances.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Điểm danh
                        </a>
                    @else
                        <a href="{{ route('student.exams.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('student.exams.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Bài tập
                        </a>
                        <a href="{{ route('student.results.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('student.results.*') ? 'border-blue-500 text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-gray-700' : 'border-transparent text-gray-600 dark:text-gray-400' }} text-base font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200">
                            Kết quả
                        </a>
                    @endif
                    <div class="sm:hidden border-t border-gray-200 dark:border-gray-700 pt-2">
                        <div class="pl-3 pr-4 py-2 text-base font-medium text-gray-500 dark:text-gray-400">
                            {{ auth()->user()->name }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <main class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
    
    <x-confirm-modal />
    
    @stack('scripts')
    
    <script>
        // Dark mode toggle functionality
        if (localStorage.getItem('darkMode') === 'enabled' || 
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('darkMode', 'disabled');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('darkMode', 'enabled');
            }
        }
        
        // Mobile menu toggle functionality
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('mobile-menu-icon-open');
            const iconClose = document.getElementById('mobile-menu-icon-close');
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                iconOpen.classList.add('hidden');
                iconClose.classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
                iconOpen.classList.remove('hidden');
                iconClose.classList.add('hidden');
            }
        }
        
        // Logout confirmation
        function confirmLogout() {
            showConfirmModal(
                'Bạn có chắc chắn muốn đăng xuất?',
                () => document.getElementById('logout-form').submit(),
                'Xác nhận đăng xuất',
                'Đăng xuất',
                'red'
            );
        }
    </script>
</body>
</html>
