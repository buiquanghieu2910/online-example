<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\IExamTakingService;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function __construct(
        private IExamTakingService $examTakingService
    ) {}

    public function index()
    {
        // Get latest result for each exam
        $results = \App\Models\UserExam::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->with('exam')
            ->get()
            ->groupBy('exam_id')
            ->map(function ($attempts) {
                return $attempts->sortByDesc('completed_at')->first();
            })
            ->sortByDesc('completed_at')
            ->values();

        return view('user.results.index', compact('results'));
    }

    public function show(int $id)
    {
        $userExam = $this->examTakingService->getResultById($id);

        if (!$userExam || $userExam->user_id !== Auth::id()) {
            return redirect()->route('user.results.index')
                ->with('error', 'Không tìm thấy kết quả bài thi.');
        }

        // Check if this is the latest attempt for this exam
        $latestAttempt = \App\Models\UserExam::where('user_id', Auth::id())
            ->where('exam_id', $userExam->exam_id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        // If trying to view an old attempt, show error
        if ($latestAttempt && $latestAttempt->id !== $userExam->id) {
            return redirect()->route('user.results.index')
                ->with('error', 'Không tìm thấy kết quả bài thi.');
        }

        return view('user.results.show', compact('userExam'));
    }
}
