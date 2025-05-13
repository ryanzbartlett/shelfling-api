<?php

namespace App\Http\Requests;

use App\Enums\LibraryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LibraryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'type' => ['required', Rule::enum(LibraryType::class)],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
