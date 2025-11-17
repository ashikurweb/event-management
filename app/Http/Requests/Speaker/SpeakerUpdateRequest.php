<?php

namespace App\Http\Requests\Speaker;

use Illuminate\Foundation\Http\FormRequest;

class SpeakerUpdateRequest extends FormRequest
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
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max per image
            'remove_photos' => 'nullable|array', // For removing photos by URL
            'title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|url',
            'specialties' => 'nullable|array',
            'specialties.*' => 'nullable|string|max:100',
            'is_featured' => 'boolean',
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
            'name.required' => 'Speaker name is required.',
            'name.max' => 'Speaker name cannot exceed 255 characters.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'photos.array' => 'Photos must be an array.',
            'photos.*.image' => 'Each photo must be an image file.',
            'photos.*.mimes' => 'Each photo must be a jpeg, jpg, png, gif, or webp file.',
            'photos.*.max' => 'Each photo size cannot exceed 5MB.',
            'website.url' => 'Please enter a valid website URL.',
            'social_links.array' => 'Social links must be an array.',
            'social_links.*.url' => 'Each social link must be a valid URL.',
            'specialties.array' => 'Specialties must be an array.',
            'user_id.exists' => 'Selected user does not exist.',
        ];
    }
}

