<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserExam extends Model
{
    protected $fillable = [
        'user_id',
        'exam_id',
        'started_at',
        'completed_at',
        'score',
        'status',
        'grading_status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
