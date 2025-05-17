<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RemoveLibraryUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    public function authorize(): bool
    {
        $userToRemove = User::find($this->input('user_id'));
        $currentUser = $this->user();

        // Check if the current user can update
        $canUpdate = $currentUser->can('update', $this->route('library'));

        // Prevent removing self
        $isRemovingSelf = $currentUser->id === $userToRemove->id;

        return $canUpdate && !$isRemovingSelf;
    }
}
