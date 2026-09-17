<?php

namespace App\Http\Requests\ReferralCodes;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateReferralCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Admin only.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:referral_codes,code'],
            'partner_id' => ['required', 'integer', 'exists:partners,id'],
            'sub_partner_id' => ['nullable', 'integer', 'exists:partners,id'],
        ];
    }
}
