<?php

namespace App\Http\Requests\Partners;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePartnerRequest extends FormRequest
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
     * Note: partner_code is excluded pending confirmation (NEEDS BUSINESS CONFIRMATION #1).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
