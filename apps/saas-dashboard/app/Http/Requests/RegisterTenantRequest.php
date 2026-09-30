<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterTenantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:120'],
            'business_name' => ['required', 'string', 'max:160'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/',
                Rule::notIn(config('tenancy.reserved_usernames', [])),
                Rule::unique('businesses', 'slug'),
            ],
            'plan_slug' => ['nullable', 'string', 'exists:plans,slug'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge([
                'username' => str($this->input('username'))->lower()->trim()->slug('-')->toString(),
            ]);
        }
    }
}
