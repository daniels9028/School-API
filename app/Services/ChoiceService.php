<?php

namespace App\Services;

use App\Models\Choice;
use App\Models\Question;

class ChoiceService
{
    public function getAllByQuestion(Question $question)
    {
        return $question->choices()->get();
    }

    public function store(Question $question, array $data): Choice
    {
        return $question->choices()->create($data);
    }

    public function update(Choice $choice, array $data): Choice
    {
        $choice->update($data);

        return $choice;
    }

    public function delete(Choice $choice): void
    {
        $choice->delete();
    }
}
