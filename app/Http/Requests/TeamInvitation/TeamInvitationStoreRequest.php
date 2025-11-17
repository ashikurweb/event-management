<?php

namespace App\Http\Requests\TeamInvitation;

use Illuminate\Foundation\Http\FormRequest;

class TeamInvitationStoreRequest extends FormRequest
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
            'team_id' => 'required|exists:teams,id',
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,manager,member',
            'token' => 'nullable|string|max:100|unique:team_invitations,token',
            'invited_by' => 'nullable|exists:users,id',
            'status' => 'nullable|in:pending,accepted,rejected,expired',
            'expires_at' => 'nullable|date|after:now',
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
            'team_id.required' => 'Team is required.',
            'team_id.exists' => 'Selected team does not exist.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'role.required' => 'Role is required.',
            'role.in' => 'Role must be one of: admin, manager, member.',
            'token.unique' => 'This token is already in use.',
            'invited_by.exists' => 'Selected inviter does not exist.',
            'status.in' => 'Status must be one of: pending, accepted, rejected, expired.',
            'expires_at.date' => 'Expiration date must be a valid date.',
            'expires_at.after' => 'Expiration date must be in the future.',
        ];
    }
}

