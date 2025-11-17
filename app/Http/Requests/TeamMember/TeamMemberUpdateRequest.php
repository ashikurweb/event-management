<?php

namespace App\Http\Requests\TeamMember;

use Illuminate\Foundation\Http\FormRequest;

class TeamMemberUpdateRequest extends FormRequest
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
            'team_id' => 'sometimes|required|exists:teams,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'role' => 'sometimes|required|in:owner,admin,manager,member',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'invited_by' => 'nullable|exists:users,id',
            'joined_at' => 'nullable|date',
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
            'user_id.required' => 'User is required.',
            'user_id.exists' => 'Selected user does not exist.',
            'role.required' => 'Role is required.',
            'role.in' => 'Role must be one of: owner, admin, manager, member.',
            'invited_by.exists' => 'Selected inviter does not exist.',
            'joined_at.date' => 'Joined date must be a valid date.',
        ];
    }
}

