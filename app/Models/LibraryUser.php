<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\LibraryRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LibraryUser extends Pivot
{
    protected $table = 'library_user';

    protected $casts = [
        'role' => LibraryRole::class,
    ];

    public function isOwner(User $user): bool
    {
        return $this->role === LibraryRole::OWNER;
    }

    public function isEditor(User $user): bool
    {
        return $this->role === LibraryRole::EDITOR;
    }

    public function isViewer(User $user): bool
    {
        return $this->role === LibraryRole::VIEWER;
    }
}
