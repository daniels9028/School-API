<?php

namespace App\Http\Requests\QuizSubmission;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:questions,id'],
            'answers.*.choice_id' => ['nullable', 'exists:choices,id'],
            'answers.*.answer_text' => ['nullable', 'string']
        ];
    }
}
