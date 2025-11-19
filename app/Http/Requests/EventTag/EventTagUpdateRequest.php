<?php

namespace App\Http\Requests\EventTag;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventTagUpdateRequest extends FormRequest
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
        $tagId = $this->route('eventTag')->id ?? null;

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('event_tags', 'name')->ignore($tagId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('event_tags', 'slug')->ignore($tagId),
            ],
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
            'name.required' => 'Tag name is required.',
            'name.max' => 'Tag name cannot exceed 50 characters.',
            'name.unique' => 'This tag name already exists.',
            'slug.max' => 'Slug cannot exceed 50 characters.',
            'slug.unique' => 'This slug is already taken.',
        ];
    }
}

