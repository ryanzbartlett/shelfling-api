<?php

namespace App\Models;

use App\Enums\LibraryRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function libraries()
    {
        return $this->belongsToMany(Library::class)
            ->withPivot('role')
            ->using(LibraryUser::class)
            ->withTimestamps();
    }

    public function libraryRole(Library $library): ?LibraryRole
    {
        $pivot = $this->libraries()
            ->where('libraries.id', $library->id)
            ->first()?->pivot;
        return $pivot?->role;
    }

    public function hasLibraryRoleAtLeast(Library $library, LibraryRole $role): bool
    {
        return $this->libraryRole($library)?->level() >= $role->level();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
