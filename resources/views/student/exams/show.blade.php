@extends('layouts.app')

@section('title', $exam->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $exam->title }}</h2>
        
        @if($exam->description)
            <p class="text-gray-700 dark:text-gray-300 mb-6">{{ $exam->description }}</p>
        @endif

        <div class="border-t border-b border-gray-200 dark:border-gray-700 py-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Thông tin bài tập</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Thời gian</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $exam->duration }} phút</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tổng số câu hỏi</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $exam->questions->count() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Điểm tối thiểu để đạt</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $exam->min_score }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Trạng thái</p>
                    <p class="text-lg font-semibold text-green-600 dark:text-green-400">Sẵn sàng</p>
                </div>
            </div>
        </div>

        @if($exam->start_time || $exam->end_time)
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">Lịch thi</h4>
                @if($exam->start_time)
                    <p class="text-sm text-blue-800 dark:text-blue-300">Bắt đầu từ: {{ $exam->start_time->format('d/m/Y H:i') }}</p>
                @endif
                @if($exam->end_time)
                    <p class="text-sm text-blue-800 dark:text-blue-300">Kết thúc lúc: {{ $exam->end_time->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        @endif

        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
            <h4 class="font-semibold text-yellow-900 dark:text-yellow-300 mb-2">Hướng dẫn</h4>
            <ul class="list-disc list-inside text-sm text-yellow-800 dark:text-yellow-300 space-y-1">
                <li>Bạn phải hoàn thành bài thi trong vòng {{ $exam->duration }} phút</li>
                <li>Khi bắt đầu thi, đồng hồ đếm ngược sẽ tự động bắt đầu</li>
                <li>Bạn có thể di chuyển giữa các câu hỏi tự do</li>
                <li>Hãy chắc chắn nhấn "Nộp bài" khi hoàn thành</li>
                <li>Bài thi của bạn sẽ tự động nộp khi hết thời gian</li>
            </ul>
        </div>

        <div class="flex space-x-4">
            <form action="{{ route('student.exams.start', $exam) }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg transition">
                    Bắt đầu thi
                </button>
            </form>
            <a href="{{ route('student.exams.index') }}" 
               class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 font-bold py-3 px-6 rounded-lg transition">
                Quay lại
            </a>
        </div>
    </div>
</div>
@endsection
