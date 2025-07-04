<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['quiz_submission_id', 'question_id', 'answer', 'is_correct'];

    public function quizSubmission()
    {
        return $this->belongsTo(QuizSubmission::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
