<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorUpdateRequest extends FormRequest
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
        $vendorId = $this->route('vendor')->id;

        return [
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('vendors', 'email')->ignore($vendorId),
            ],
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
            'remove_logo' => 'nullable|boolean',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'category' => 'nullable|string|max:100',
            'rating' => 'nullable|numeric|min:0|max:5|decimal:0,2',
            'is_verified' => 'boolean',
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
            'name.required' => 'Vendor name is required.',
            'name.max' => 'Vendor name cannot exceed 255 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be a jpeg, jpg, png, gif, or webp file.',
            'logo.max' => 'Logo size cannot exceed 5MB.',
            'website.url' => 'Please enter a valid website URL.',
            'website.max' => 'Website URL cannot exceed 255 characters.',
            'category.max' => 'Category cannot exceed 100 characters.',
            'rating.numeric' => 'Rating must be a number.',
            'rating.min' => 'Rating cannot be less than 0.',
            'rating.max' => 'Rating cannot be greater than 5.',
        ];
    }
}

