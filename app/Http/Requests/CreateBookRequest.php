<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required'],
            'author' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('update-books', $this->route('library'));
    }
}
