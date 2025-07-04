<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'title', 'content', 'video_url', 'order', 'created_by'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function completedByUsers()
    {
        return $this->belongsToMany(User::class, 'lesson_completions');
    }
}
