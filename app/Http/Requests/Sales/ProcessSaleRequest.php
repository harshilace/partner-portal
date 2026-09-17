<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class ProcessSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'product_plan_id' => ['required', 'integer', 'exists:product_plans,id'],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'payment_options' => ['nullable', 'array'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'discounts' => ['nullable'],
            'custom_pricing' => ['nullable'],
            'commercial_rules' => ['nullable'],
        ];
    }
}
