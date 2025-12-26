<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    // Relationship: Teacher has many students (many-to-many)
    public function students()
    {
        return $this->belongsToMany(User::class, 'teacher_student', 'teacher_id', 'student_id')
            ->withTimestamps();
    }

    // Relationship: Student belongs to many teachers (many-to-many)
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_student', 'student_id', 'teacher_id')
            ->withTimestamps();
    }

    public function userExams()
    {
        return $this->hasMany(UserExam::class);
    }

    public function assignedExams()
    {
        return $this->belongsToMany(Exam::class, 'exam_user')
            ->withTimestamps()
            ->withPivot('assigned_at');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function recordedAttendances()
    {
        return $this->hasMany(Attendance::class, 'teacher_id');
    }

    public function schoolClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_student', 'student_id', 'class_id')
            ->withTimestamps();
    }

    public function teachingClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'teacher_id', 'class_id')
            ->withTimestamps();
    }
}
