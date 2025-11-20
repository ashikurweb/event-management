<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class ReviewReplyStoreRequest extends FormRequest
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
            'review_id' => 'required|exists:reviews,id',
            'comment' => 'required|string|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'review_id.required' => 'Review is required.',
            'review_id.exists' => 'Selected review does not exist.',
            'comment.required' => 'Comment is required.',
            'comment.min' => 'Comment cannot be empty.',
        ];
    }
}

