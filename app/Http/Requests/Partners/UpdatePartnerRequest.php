<?php

namespace App\Http\Requests\Partners;

use App\Domain\Partners\Partner;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $partner = $this->route('partner');

        if (! $partner instanceof Partner) {
            return false;
        }

        return (bool) $this->user()?->can('update', $partner);
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
