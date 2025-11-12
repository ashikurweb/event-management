<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest
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
        $category = $this->route('category');
        $categoryId = $category ? (is_object($category) ? $category->id : $category) : null;

        return [
            'name' => 'required|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ($value && $value == $categoryId) {
                        $fail('A category cannot be its own parent.');
                    }
                },
            ],
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
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
            'name.required' => 'Category name is required.',
            'name.max' => 'Category name cannot exceed 100 characters.',
            'slug.max' => 'Slug cannot exceed 100 characters.',
            'slug.unique' => 'This slug is already taken. Please choose a different one.',
            'color.regex' => 'Please enter a valid hex color code (e.g., #1890ff).',
            'parent_id.exists' => 'Selected parent category does not exist.',
            'display_order.integer' => 'Display order must be a number.',
            'display_order.min' => 'Display order cannot be negative.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $category = $this->route('category');
            $parentId = $this->input('parent_id');

            if ($parentId && $category && is_object($category)) {
                // Check for circular references
                $descendants = $category->children()->pluck('id')->toArray();
                if (in_array($parentId, $descendants)) {
                    $validator->errors()->add('parent_id', 'Cannot set a descendant category as parent.');
                }
            }
        });
    }
}

