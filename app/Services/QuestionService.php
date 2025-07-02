<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;

class QuestionService
{
    public function getAllByQuiz(Quiz $quiz)
    {
        return $quiz->questions()->get();
    }

    public function store(Quiz $quiz, array $data): Question
    {
        return $quiz->questions()->create($data);
    }

    public function update(Question $question, array $data): Question
    {
        $question->update($data);

        return $question;
    }

    public function delete(Question $question): void
    {
        $question->delete();
    }
}
