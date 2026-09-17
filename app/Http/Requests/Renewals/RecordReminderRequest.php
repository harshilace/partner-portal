<?php

namespace App\Http\Requests\Renewals;

use App\Domain\Renewals\Renewal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordReminderRequest extends FormRequest
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
            'milestone' => ['required', 'string', Rule::in(Renewal::validMilestones())],
        ];
    }
}
