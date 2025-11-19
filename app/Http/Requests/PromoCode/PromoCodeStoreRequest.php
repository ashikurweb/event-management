<?php

namespace App\Http\Requests\PromoCode;

use Illuminate\Foundation\Http\FormRequest;

class PromoCodeStoreRequest extends FormRequest
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
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed,free_ticket',
            'discount_value' => 'required|numeric|min:0',
            'applicable_to' => 'required|in:all,specific_events,specific_categories',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1|default:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'is_active' => 'nullable|boolean',
            'events' => 'nullable|array|required_if:applicable_to,specific_events',
            'events.*' => 'exists:events,id',
            'categories' => 'nullable|array|required_if:applicable_to,specific_categories',
            'categories.*' => 'exists:categories,id',
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
            'code.required' => 'Promo code is required.',
            'code.max' => 'Promo code cannot exceed 50 characters.',
            'code.unique' => 'This promo code already exists.',
            'discount_type.required' => 'Please select a discount type.',
            'discount_type.in' => 'Invalid discount type selected.',
            'discount_value.required' => 'Discount value is required.',
            'discount_value.numeric' => 'Discount value must be a number.',
            'discount_value.min' => 'Discount value must be at least 0.',
            'applicable_to.required' => 'Please select where this promo code applies.',
            'applicable_to.in' => 'Invalid applicable option selected.',
            'max_uses.integer' => 'Max uses must be a number.',
            'max_uses.min' => 'Max uses must be at least 1.',
            'max_uses_per_user.integer' => 'Max uses per user must be a number.',
            'max_uses_per_user.min' => 'Max uses per user must be at least 1.',
            'min_order_amount.numeric' => 'Minimum order amount must be a number.',
            'min_order_amount.min' => 'Minimum order amount must be at least 0.',
            'valid_from.required' => 'Valid from date is required.',
            'valid_from.date' => 'Please enter a valid date.',
            'valid_until.required' => 'Valid until date is required.',
            'valid_until.date' => 'Please enter a valid date.',
            'valid_until.after' => 'Valid until date must be after valid from date.',
            'events.required_if' => 'Please select at least one event.',
            'events.*.exists' => 'One or more selected events do not exist.',
            'categories.required_if' => 'Please select at least one category.',
            'categories.*.exists' => 'One or more selected categories do not exist.',
        ];
    }
}

