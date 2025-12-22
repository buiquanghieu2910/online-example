<?php

namespace App\Repositories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

interface IQuestionRepository
{
    public function findById(int $id): ?Question;
    
    public function getByExamId(int $examId): Collection;
    
    public function create(array $data): Question;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getQuestionWithAnswers(int $id): ?Question;
}
