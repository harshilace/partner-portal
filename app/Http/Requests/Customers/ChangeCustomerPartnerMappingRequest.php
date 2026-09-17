<?php

namespace App\Http\Requests\Customers;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangeCustomerPartnerMappingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Admin only via CustomerPolicy::changeMapping.
     */
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        return $this->user()?->can('changeMapping', $customer) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'partner_id' => ['required', 'integer', 'exists:partners,id'],
            'sub_partner_id' => ['nullable', 'integer', 'exists:partners,id'],
        ];
    }
}
