<?php

namespace App\Http\Requests\Leads;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateLeadFollowUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Deferred to LeadPolicy::createFollowUp (returns false for all non-Admin).
     */
    public function authorize(): bool
    {
        $lead = $this->route('lead');

        return $this->user()?->can('createFollowUp', $lead) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'follow_up_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
