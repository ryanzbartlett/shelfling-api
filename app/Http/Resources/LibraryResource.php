<?php

namespace App\Http\Resources;

use App\Models\Library;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/** @mixin Library */
class LibraryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $role = null;

        if ($user) {
            $role = $user->libraryRole($this->resource)->value;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'role' => $role,
        ];
    }
}
