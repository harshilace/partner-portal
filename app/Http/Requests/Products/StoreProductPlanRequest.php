<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductPlanRequest extends FormRequest
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
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('product_plans', 'code')->where('product_id', $productId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['nullable', 'string', 'max:50'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
