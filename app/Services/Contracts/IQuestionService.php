<?php

namespace App\Services\Contracts;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

interface IQuestionService
{
    public function getQuestionById(int $id): ?Question;
    
    public function getQuestionsByExamId(int $examId): Collection;
    
    public function createQuestion(int $examId, array $data): Question;
    
    public function updateQuestion(int $id, array $data): bool;
    
    public function deleteQuestion(int $id): bool;
    
    public function syncAnswers(Question $question, array $answers): void;
}
