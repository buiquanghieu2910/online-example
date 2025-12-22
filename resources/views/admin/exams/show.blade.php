@extends('layouts.app')

@section('title', 'Xem đề thi')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $exam->title }}</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $exam->description }}</p>
        </div>
        <span class="px-3 py-1 rounded {{ $exam->is_active ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
            {{ $exam->is_active ? 'Hoạt động' : 'Không hoạt động' }}
        </span>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Thời gian</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $exam->duration }} phút</p>
        </div>
        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Điểm tối thiểu để đạt</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $exam->min_score }}</p>
        </div>
        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Số câu hỏi</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $exam->questions->count() }}</p>
        </div>
        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Lượt thi</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $exam->userExams->count() }}</p>
        </div>
    </div>

    @if($exam->start_time || $exam->end_time)
        <div class="mb-6">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Lịch thi</h3>
            @if($exam->start_time)
                <p class="text-sm text-gray-600 dark:text-gray-400">Bắt đầu: {{ $exam->start_time->format('d/m/Y H:i') }}</p>
            @endif
            @if($exam->end_time)
                <p class="text-sm text-gray-600 dark:text-gray-400">Kết thúc: {{ $exam->end_time->format('d/m/Y H:i') }}</p>
            @endif
        </div>
    @endif

    <div class="flex space-x-3">
        <a href="{{ route('admin.questions.index', $exam) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Quản lý câu hỏi
        </a>
        <a href="{{ route('admin.exams.assign', $exam) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
            Gán người dùng
        </a>
        <a href="{{ route('admin.exams.edit', $exam) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Chỉnh sửa
        </a>
        <a href="{{ route('admin.exams.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-gray-100 font-bold py-2 px-4 rounded">
            Quay lại
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Người dùng được gán</h3>
    
    @if($exam->assignedUsers->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Người dùng</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Điểm</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Thời gian làm bài</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($exam->assignedUsers as $user)
                        @php
                            // Get latest attempt only
                            $userExam = $exam->userExams->where('user_id', $user->id)
                                ->sortByDesc('completed_at')
                                ->sortByDesc('created_at')
                                ->first();
                            $duration = null;
                            $durationMinutes = 0;
                            $durationSeconds = 0;
                            if ($userExam && $userExam->started_at && $userExam->completed_at) {
                                $totalSeconds = $userExam->started_at->diffInSeconds($userExam->completed_at);
                                $durationMinutes = floor($totalSeconds / 60);
                                $durationSeconds = $totalSeconds % 60;
                                $duration = true;
                            }
                            // Count total attempts
                            $totalAttempts = $exam->userExams->where('user_id', $user->id)->where('status', 'completed')->count();
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $user->name }} ({{ $user->username }})
                                @if($totalAttempts > 1)
                                    <a href="{{ route('admin.exams.history', [$exam, $user]) }}" 
                                        class="ml-2 text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        ({{ $totalAttempts }} lần)
                                    </a>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($userExam)
                                    @if($userExam->status === 'completed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            Đã làm
                                        </span>
                                    @elseif($userExam->status === 'in_progress')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                            Đang làm
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Chưa làm
                                        </span>
                                    @endif
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Chưa làm
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                @if($userExam && $userExam->score !== null)
                                    <span class="font-semibold {{ $userExam->score >= $exam->min_score ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $userExam->score }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($duration !== null)
                                    {{ $durationMinutes }} phút {{ $durationSeconds }} giây
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-3">
                                    @if($userExam && $userExam->status === 'completed')
                                        <form action="{{ route('admin.results.reset', $userExam) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                onclick="return confirm('Bạn có chắc chắn muốn cho người dùng này làm lại bài thi?')">
                                                Cho làm lại
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.exams.unassign', [$exam, $user]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            onclick="return confirm('Bạn có chắc chắn muốn gỡ người dùng này khỏi đề thi?')">
                                            Gỡ
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400">Chưa có người dùng nào được gán. <a href="{{ route('admin.exams.assign', $exam) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Gán ngay</a></p>
    @endif
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Các câu hỏi</h3>
    
    @forelse($exam->questions as $question)
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4 last:border-b-0">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Loại: {{ ucfirst(str_replace('_', ' ', $question->question_type)) }} | Điểm: {{ $question->points }}</p>
                </div>
            </div>
            
            @if($question->question_type !== 'essay')
                <div class="mt-3 ml-6">
                    @foreach($question->answers as $answer)
                        <div class="flex items-center mb-2">
                            @if($answer->is_correct)
                                <span class="text-green-600 dark:text-green-400 mr-2">✓</span>
                            @else
                                <span class="text-gray-400 dark:text-gray-500 mr-2">○</span>
                            @endif
                            <span class="{{ $answer->is_correct ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $answer->answer_text }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <p class="text-gray-500 dark:text-gray-400">Chưa có câu hỏi nào.</p>
    @endforelse
</div>
@endsection
