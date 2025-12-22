@extends('layouts.app')

@section('title', 'Làm bài thi - ' . $exam->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg mb-6 sticky top-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $exam->title }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Câu hỏi <span id="currentQuestion">1</span> / {{ $questions->count() }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600 dark:text-gray-400">Thời gian còn lại</p>
                <p id="timer" class="text-2xl font-bold text-blue-600 dark:text-blue-400"></p>
            </div>
        </div>
    </div>

    <form action="{{ route('user.exams.submit', $exam) }}" method="POST" id="examForm">
        @csrf

        @foreach($questions as $index => $question)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6 question-card" data-question="{{ $index + 1 }}" style="{{ $index > 0 ? 'display: none;' : '' }}">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Câu hỏi {{ $index + 1 }} ({{ $question->points }} điểm)
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300">{{ $question->question_text }}</p>
                    @if($question->image_url)
                        <div class="mt-3">
                            <img src="{{ minio_image_base64($question->image_url) }}" alt="Question image" class="max-w-full rounded-md shadow">
                        </div>
                    @endif
                </div>

                @if($question->question_type === 'multiple_choice' || $question->question_type === 'true_false')
                    <div class="space-y-3">
                        @foreach($question->answers as $answer)
                            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}"
                                    class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500">
                                <span class="text-gray-900 dark:text-gray-100">{{ $answer->answer_text }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif($question->question_type === 'essay')
                    <textarea name="answers[{{ $question->id }}]" rows="6" 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Nhập câu trả lời của bạn..."></textarea>
                @endif
            </div>
        @endforeach

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <button type="button" id="prevBtn" onclick="changeQuestion(-1)" 
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-gray-100 font-bold py-2 px-6 rounded">
                    Câu trước
                </button>
                <button type="button" id="nextBtn" onclick="changeQuestion(1)" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Câu tiếp
                </button>
                <button type="submit" id="submitBtn" style="display: none;"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded"
                    onclick="return confirm('Bạn có chắc chắn muốn nộp bài?')">
                    Nộp bài
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let currentQuestion = 1;
const totalQuestions = {{ $questions->count() }};
let timeRemaining = {{ $timeRemaining }};

function updateQuestionDisplay() {
    document.querySelectorAll('.question-card').forEach(card => {
        card.style.display = 'none';
    });
    
    const current = document.querySelector(`.question-card[data-question="${currentQuestion}"]`);
    if (current) {
        current.style.display = 'block';
    }
    
    document.getElementById('currentQuestion').textContent = currentQuestion;
    
    document.getElementById('prevBtn').style.display = currentQuestion === 1 ? 'none' : 'inline-block';
    document.getElementById('nextBtn').style.display = currentQuestion === totalQuestions ? 'none' : 'inline-block';
    document.getElementById('submitBtn').style.display = currentQuestion === totalQuestions ? 'inline-block' : 'none';
}

function changeQuestion(direction) {
    currentQuestion += direction;
    if (currentQuestion < 1) currentQuestion = 1;
    if (currentQuestion > totalQuestions) currentQuestion = totalQuestions;
    updateQuestionDisplay();
}

function updateTimer() {
    const hours = Math.floor(timeRemaining / 3600);
    const minutes = Math.floor((timeRemaining % 3600) / 60);
    const seconds = timeRemaining % 60;
    
    const display = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('timer').textContent = display;
    
    if (timeRemaining <= 300) {
        document.getElementById('timer').classList.add('text-red-600');
        document.getElementById('timer').classList.remove('text-blue-600');
    }
    
    if (timeRemaining <= 0) {
        alert('Hết giờ! Bài thi của bạn sẽ tự động nộp.');
        document.getElementById('examForm').submit();
        return;
    }
    
    timeRemaining--;
    setTimeout(updateTimer, 1000);
}

updateTimer();
updateQuestionDisplay();

window.addEventListener('beforeunload', function (e) {
    if (timeRemaining > 0) {
        e.preventDefault();
        e.returnValue = 'Bạn có chắc muốn thoát? Tiến trình của bạn sẽ bị mất.';
    }
});
</script>
@endpush
@endsection
