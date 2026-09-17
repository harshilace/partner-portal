<?php

namespace App\Http\Requests\Leads;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadFollowUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Deferred to LeadPolicy::updateFollowUp (returns false for all non-Admin).
     */
    public function authorize(): bool
    {
        $lead = $this->route('lead');

        return $this->user()?->can('updateFollowUp', $lead) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     * Note: status is omitted pending confirmation (NEEDS BUSINESS CONFIRMATION #14).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'follow_up_at' => ['sometimes', 'required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
