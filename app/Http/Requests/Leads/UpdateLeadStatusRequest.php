<?php

namespace App\Http\Requests\Leads;

use App\Domain\Leads\Enums\LeadStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateLeadStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Deferred to LeadPolicy::update (returns false for all non-Admin).
     */
    public function authorize(): bool
    {
        $lead = $this->route('lead');

        return $this->user()?->can('update', $lead) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(LeadStatus::class)],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
