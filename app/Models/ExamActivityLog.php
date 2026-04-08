<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamActivityLog extends Model
{
    protected $fillable = [
        'user_exam_id',
        'user_id',
        'exam_id',
        'event',
        'meta',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function userExam()
    {
        return $this->belongsTo(UserExam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
