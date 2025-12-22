@extends('layouts.app')

@section('title', 'Kết quả bài thi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 mb-6">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ $userExam->exam->title }}</h2>
            <p class="text-gray-600 dark:text-gray-400">Hoàn thành lúc {{ $userExam->completed_at->format('d/m/Y H:i') }}</p>
        </div>

        @if($userExam->grading_status === 'pending_review')
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-yellow-800 text-center font-semibold">
                    ⏳ Bài thi của bạn đang chờ giáo viên chấm điểm tự luận. Kết quả sẽ được cập nhật sau.
                </p>
            </div>
        @endif

        <div class="border-t border-b border-gray-200 dark:border-gray-700 py-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Điểm của bạn</p>
                    @if($userExam->score !== null)
                        <p class="text-3xl font-bold {{ $userExam->score >= $userExam->exam->min_score ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $userExam->score }}
                        </p>
                    @else
                        <p class="text-xl font-semibold text-gray-400 dark:text-gray-500">
                            Chờ chấm
                        </p>
                    @endif
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Điểm tối thiểu để đạt</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $userExam->exam->min_score }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Kết quả</p>
                    @if($userExam->score !== null)
                        @if($userExam->score >= $userExam->exam->min_score)
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">ĐẠT</p>
                        @else
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">KHÔNG ĐẠT</p>
                        @endif
                    @else
                        <p class="text-xl font-semibold text-gray-400 dark:text-gray-500">-</p>
                    @endif
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Thời gian</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        @php
                            $totalSeconds = $userExam->started_at->diffInSeconds($userExam->completed_at);
                            $minutes = floor($totalSeconds / 60);
                            $seconds = $totalSeconds % 60;
                        @endphp
                        {{ $minutes }} phút {{ $seconds }} giây
                    </p>
                </div>
            </div>
        </div>

        @if($userExam->grading_status !== 'pending_review')
            @if($userExam->score !== null && $userExam->score >= $userExam->exam->min_score)
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
                    <p class="text-green-800 dark:text-green-300 text-center font-semibold">
                        🎉 Chúc mừng! Bạn đã vượt qua bài thi này.
                    </p>
                </div>
            @elseif($userExam->score !== null)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                    <p class="text-red-800 dark:text-red-300 text-center font-semibold">
                        Rất tiếc, bạn chưa vượt qua bài thi này. Hãy tiếp tục học tập và thử lại!
                    </p>
                </div>
            @endif
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Xem lại câu trả lời</h3>

        @foreach($userExam->userAnswers as $index => $userAnswer)
            <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6 last:border-b-0">
                <div class="flex justify-between items-start mb-3">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Câu hỏi {{ $index + 1 }}
                    </h4>
                    @if($userAnswer->is_correct !== null)
                        <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $userAnswer->is_correct ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                            {{ $userAnswer->is_correct ? '✓ Đúng' : '✗ Sai' }}
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                            Tự luận - Chưa chấm
                        </span>
                    @endif
                </div>

                <p class="text-gray-700 dark:text-gray-300 mb-4">{{ $userAnswer->question->question_text }}</p>

                @if($userAnswer->question->image_url)
                    <div class="mb-4">
                        <img src="{{ minio_image_base64($userAnswer->question->image_url) }}" 
                             alt="Hình ảnh câu hỏi" 
                             class="max-w-full h-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    </div>
                @endif

                @if($userAnswer->question->question_type === 'essay')
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Câu trả lời của bạn:</p>
                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $userAnswer->essay_answer ?? 'Không có câu trả lời' }}</p>
                    </div>

                    @if($userAnswer->essay_score !== null)
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Nhận xét từ giáo viên:</p>
                                <span class="px-3 py-1 bg-blue-600 text-white rounded-full text-sm font-semibold">
                                    Điểm: {{ $userAnswer->essay_score }}/100
                                </span>
                            </div>
                            @if($userAnswer->admin_feedback)
                                <p class="text-blue-900 dark:text-blue-200 whitespace-pre-wrap">{{ $userAnswer->admin_feedback }}</p>
                            @else
                                <p class="text-blue-700 dark:text-blue-300 italic">Giáo viên chưa để lại nhận xét.</p>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="space-y-2">
                        @foreach($userAnswer->question->answers as $answer)
                            <div class="flex items-center p-3 rounded-lg {{ $answer->is_correct ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : ($userAnswer->answer_id == $answer->id ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-gray-50 dark:bg-gray-900') }}">
                                @if($answer->is_correct)
                                    <span class="text-green-600 dark:text-green-400 font-bold mr-2">✓</span>
                                @elseif($userAnswer->answer_id == $answer->id)
                                    <span class="text-red-600 dark:text-red-400 font-bold mr-2">✗</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 mr-2">○</span>
                                @endif
                                <span class="{{ $answer->is_correct ? 'text-green-800 dark:text-green-300 font-semibold' : ($userAnswer->answer_id == $answer->id ? 'text-red-800 dark:text-red-300' : 'text-gray-700 dark:text-gray-300') }}">
                                    {{ $answer->answer_text }}
                                    @if($answer->is_correct)
                                        <span class="text-sm">(Đáp án đúng)</span>
                                    @endif
                                    @if($userAnswer->answer_id == $answer->id && !$answer->is_correct)
                                        <span class="text-sm">(Câu trả lời của bạn)</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        <div class="flex justify-center mt-8">
            <a href="{{ route('user.results.index') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                Quay lại kết quả
            </a>
        </div>
    </div>
</div>
@endsection
