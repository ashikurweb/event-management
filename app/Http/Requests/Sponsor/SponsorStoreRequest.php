<?php

namespace App\Http\Requests\Sponsor;

use Illuminate\Foundation\Http\FormRequest;

class SponsorStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'tier' => 'required|in:platinum,gold,silver,bronze,partner',
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
            'name.required' => 'Sponsor name is required.',
            'name.max' => 'Sponsor name cannot exceed 255 characters.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be a jpeg, jpg, png, gif, or webp file.',
            'logo.max' => 'Logo size cannot exceed 5MB.',
            'website.url' => 'Please enter a valid website URL.',
            'website.max' => 'Website URL cannot exceed 255 characters.',
            'tier.required' => 'Sponsor tier is required.',
            'tier.in' => 'Tier must be one of: platinum, gold, silver, bronze, partner.',
            'display_order.integer' => 'Display order must be an integer.',
            'display_order.min' => 'Display order cannot be less than 0.',
        ];
    }
}

