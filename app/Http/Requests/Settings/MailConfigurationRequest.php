<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class MailConfigurationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mail_mailer' => ['required', 'string', 'max:255'],
            'mail_host' => ['required', 'string', 'max:255'],
            'mail_port' => ['required', 'string', 'max:10'],
            'mail_username' => ['required', 'string', 'max:255'],
            'mail_password' => ['required', 'string', 'max:255'],
            'mail_encryption' => ['required', 'string', 'in:tls,ssl,none'],
            'mail_from_address' => ['required', 'email', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'mail_mailer.required' => 'Please enter mail mailer',
            'mail_host.required' => 'Please enter mail host',
            'mail_port.required' => 'Please enter mail port',
            'mail_username.required' => 'Please enter mail username',
            'mail_password.required' => 'Please enter mail password',
            'mail_encryption.required' => 'Please enter mail encryption',
            'mail_encryption.in' => 'Mail encryption must be tls, ssl, or none',
            'mail_from_address.required' => 'Please enter mail from address',
            'mail_from_address.email' => 'Please enter a valid email address',
        ];
    }
}

