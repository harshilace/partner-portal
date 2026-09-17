<?php

namespace App\Http\Requests\Auth;

use App\Domain\Authentication\Actions\AuthenticateUserAction;
use App\Domain\Authentication\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials using the action.
     */
    public function authenticate(AuthenticateUserAction $action): User
    {
        return $action->execute(
            email: $this->string('email')->toString(),
            password: $this->string('password')->toString(),
            remember: $this->boolean('remember'),
            request: $this
        );
    }
}
