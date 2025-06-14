<?php

namespace App\Policies;

use App\Enums\LibraryRole;
use App\Models\Library;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LibraryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Library $library): bool
    {
        $hasAccess = $library->users()
            ->where('user_id', $user->id)
            ->exists();

        $canView = $user->hasLibraryRoleAtLeast($library, LibraryRole::VIEWER);

        return $hasAccess && $canView;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Library $library): bool
    {
        $hasAccess = $library->users()
            ->where('user_id', $user->id)
            ->exists();

        $isOwner = $user->hasLibraryRoleAtLeast($library, LibraryRole::OWNER);

        return $hasAccess && $isOwner;
    }

    public function updateBooks(User $user, Library $library): bool
    {
        $hasAccess = $library->users()
            ->where('user_id', $user->id)
            ->exists();

        $isEditor = $user->hasLibraryRoleAtLeast($library, LibraryRole::EDITOR);

        return $hasAccess && $isEditor;
    }

    public function delete(User $user, Library $library): bool
    {
        $hasAccess = $library->users()
            ->where('user_id', $user->id)
            ->exists();

        $isOwner = $user->hasLibraryRoleAtLeast($library, LibraryRole::OWNER);

        return $hasAccess && $isOwner;
    }
}
