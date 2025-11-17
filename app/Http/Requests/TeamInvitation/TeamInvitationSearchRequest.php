<?php

namespace App\Http\Requests\TeamInvitation;

use Illuminate\Foundation\Http\FormRequest;

class TeamInvitationSearchRequest extends FormRequest
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
            'search' => 'nullable|string|max:255',
            'team_id' => 'nullable|integer|exists:teams,id',
            'status' => 'nullable|string|in:pending,accepted,rejected,expired',
            'role' => 'nullable|string|in:admin,manager,member',
            'invited_by' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'expired' => 'nullable|boolean',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|string|in:email,role,status,expires_at,created_at,updated_at',
            'sort_order' => 'nullable|string|in:asc,desc',
        ];
    }
}

