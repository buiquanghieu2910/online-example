<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $fillable = [
        'user_exam_id',
        'question_id',
        'answer_id',
        'essay_answer',
        'is_correct',
        'admin_feedback',
        'essay_score',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function userExam()
    {
        return $this->belongsTo(UserExam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }
}
