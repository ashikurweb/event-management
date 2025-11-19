<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class EventSearchRequest extends FormRequest
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
            'search' => 'nullable|string|max:255',
            'organizer_id' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'event_type' => 'nullable|in:online,offline,hybrid',
            'status' => 'nullable|in:draft,published,cancelled,completed,postponed',
            'visibility' => 'nullable|in:public,private,unlisted',
            'is_featured' => 'nullable|boolean',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'tag_id' => 'nullable|exists:event_tags,id',
            'sort_by' => 'nullable|in:title,start_date,end_date,created_at,updated_at,view_count',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
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
            'organizer_id.exists' => 'Selected organizer does not exist.',
            'category_id.exists' => 'Selected category does not exist.',
            'event_type.in' => 'Invalid event type selected.',
            'status.in' => 'Invalid status selected.',
            'visibility.in' => 'Invalid visibility option selected.',
            'date_to.after_or_equal' => 'End date must be after or equal to start date.',
            'tag_id.exists' => 'Selected tag does not exist.',
            'sort_by.in' => 'Invalid sort field selected.',
            'sort_order.in' => 'Sort order must be either asc or desc.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
        ];
    }
}

