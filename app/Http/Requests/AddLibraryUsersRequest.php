<?php

namespace App\Http\Requests;

use App\Enums\LibraryRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddLibraryUsersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'users' => ['required', 'array', 'min:1'],
            'users.*.email' => ['required', 'email', 'exists:users,email'],
            'users.*.role' => ['required', Rule::enum(LibraryRole::class)],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('library'));
    }
}
