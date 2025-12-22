<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'title',
        'description',
        'duration',
        'min_score',
        'is_active',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function userExams()
    {
        return $this->hasMany(UserExam::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'exam_user')
            ->withTimestamps()
            ->withPivot('assigned_at');
    }
}
