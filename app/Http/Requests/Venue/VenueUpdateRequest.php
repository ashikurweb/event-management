<?php

namespace App\Http\Requests\Venue;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VenueUpdateRequest extends FormRequest
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
        $venueId = $this->route('venue')->id;

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('venues', 'slug')->ignore($venueId),
            ],
            'description' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'capacity' => 'nullable|integer|min:1',
            'facilities' => 'nullable|array',
            'facilities.*' => 'nullable|string|max:100',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max per image
            'remove_images' => 'nullable|array', // For removing images by URL
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'is_verified' => 'boolean',
            'rating' => 'nullable|numeric|min:0|max:5|decimal:0,2',
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
            'name.required' => 'Venue name is required.',
            'name.max' => 'Venue name cannot exceed 255 characters.',
            'slug.unique' => 'This slug is already in use.',
            'slug.max' => 'Slug cannot exceed 255 characters.',
            'address.required' => 'Address is required.',
            'city.required' => 'City is required.',
            'city.max' => 'City cannot exceed 100 characters.',
            'country.required' => 'Country is required.',
            'country.max' => 'Country cannot exceed 100 characters.',
            'postal_code.max' => 'Postal code cannot exceed 20 characters.',
            'latitude.numeric' => 'Latitude must be a number.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a number.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'capacity.integer' => 'Capacity must be an integer.',
            'capacity.min' => 'Capacity must be at least 1.',
            'images.array' => 'Images must be an array.',
            'images.*.image' => 'Each image must be an image file.',
            'images.*.mimes' => 'Each image must be a jpeg, jpg, png, gif, or webp file.',
            'images.*.max' => 'Each image size cannot exceed 5MB.',
            'contact_name.max' => 'Contact name cannot exceed 255 characters.',
            'contact_email.email' => 'Please enter a valid email address.',
            'contact_email.max' => 'Email cannot exceed 255 characters.',
            'contact_phone.max' => 'Phone number cannot exceed 20 characters.',
            'website.url' => 'Please enter a valid website URL.',
            'website.max' => 'Website URL cannot exceed 255 characters.',
            'rating.numeric' => 'Rating must be a number.',
            'rating.min' => 'Rating cannot be less than 0.',
            'rating.max' => 'Rating cannot be greater than 5.',
        ];
    }
}

