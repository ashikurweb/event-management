<?php

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;

class SurveyUpdateRequest extends FormRequest
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
            'event_id' => 'required|exists:events,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'opens_at' => 'nullable|date',
            'closes_at' => 'nullable|date|after:opens_at',
            'status' => 'required|in:draft,active,closed',
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
            'event_id.required' => 'Event is required.',
            'event_id.exists' => 'Selected event does not exist.',
            'title.required' => 'Survey title is required.',
            'title.max' => 'Survey title cannot exceed 255 characters.',
            'closes_at.after' => 'Closing date must be after opening date.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: draft, active, closed.',
        ];
    }
}

