<?php

namespace App\Http\Requests\Certificate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CertificateUpdateRequest extends FormRequest
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
        $certificate = $this->route('certificate');
        $certificateId = $certificate ? (is_object($certificate) ? $certificate->id : $certificate) : null;

        return [
            'event_id' => 'required|exists:events,id',
            'user_id' => 'required|exists:users,id',
            'certificate_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('certificates', 'certificate_number')->ignore($certificateId),
            ],
            'template_path' => 'nullable|string|max:255',
            'file_path' => 'nullable|string|max:255',
            'issued_at' => 'nullable|date',
            'downloaded_at' => 'nullable|date',
            'verification_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('certificates', 'verification_code')->ignore($certificateId),
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
            'event_id.required' => 'Please select an event.',
            'event_id.exists' => 'Selected event does not exist.',
            'user_id.required' => 'Please select a user.',
            'user_id.exists' => 'Selected user does not exist.',
            'certificate_number.max' => 'Certificate number cannot exceed 100 characters.',
            'certificate_number.unique' => 'This certificate number is already taken.',
            'template_path.max' => 'Template path cannot exceed 255 characters.',
            'file_path.max' => 'File path cannot exceed 255 characters.',
            'issued_at.date' => 'Please enter a valid date.',
            'downloaded_at.date' => 'Please enter a valid date.',
            'verification_code.max' => 'Verification code cannot exceed 100 characters.',
            'verification_code.unique' => 'This verification code is already taken.',
        ];
    }
}

