<?php

namespace App\Http\Requests\Partners;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Admin-only authority pending confirmation (NEEDS BUSINESS CONFIRMATION #11).
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
