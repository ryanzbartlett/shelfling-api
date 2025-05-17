<?php

use App\Enums\LibraryRole;
use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('library roles have correct level hierarchy', function () {
    // Test the role hierarchy
    expect(LibraryRole::VIEWER->level())->toBeLessThan(LibraryRole::EDITOR->level())
        ->and(LibraryRole::EDITOR->level())->toBeLessThan(LibraryRole::OWNER->level())
        ->and(LibraryRole::VIEWER->level())->toBe(1)
        ->and(LibraryRole::EDITOR->level())->toBe(2)
        ->and(LibraryRole::OWNER->level())->toBe(3);

    // Specific levels
});

test('libraryRole method returns correct role enum', function () {
    $user = User::factory()->create();
    $library = Library::factory()->create();

    // User has no role initially
    expect($user->libraryRole($library))->toBeNull();

    // Assign viewer role
    $library->users()->attach($user, ['role' => LibraryRole::VIEWER]);
    $user->refresh();
    expect($user->libraryRole($library))->toBe(LibraryRole::VIEWER);

    // Change to editor role
    $library->users()->updateExistingPivot($user->id, ['role' => LibraryRole::EDITOR]);
    $user->refresh();
    expect($user->libraryRole($library))->toBe(LibraryRole::EDITOR);

    // Change to owner role
    $library->users()->updateExistingPivot($user->id, ['role' => LibraryRole::OWNER]);
    $user->refresh();
    expect($user->libraryRole($library))->toBe(LibraryRole::OWNER);
});

test('hasLibraryRoleAtLeast checks role levels correctly', function () {
    $user = User::factory()->create();
    $library = Library::factory()->create();

    // User has no role initially
    expect($user->hasLibraryRoleAtLeast($library, LibraryRole::VIEWER))->toBeFalse()
        ->and($user->hasLibraryRoleAtLeast($library, LibraryRole::EDITOR))->toBeFalse()
        ->and($user->hasLibraryRoleAtLeast($library, LibraryRole::OWNER))->toBeFalse();

    // VIEWER role tests
    $library->users()->attach($user, ['role' => LibraryRole::VIEWER]);
    $user->refresh();

    expect($user->hasLibraryRoleAtLeast($library, LibraryRole::VIEWER))->toBeTrue()
        ->and($user->hasLibraryRoleAtLeast($library, LibraryRole::EDITOR))->toBeFalse()
        ->and($user->hasLibraryRoleAtLeast($library, LibraryRole::OWNER))->toBeFalse();

    // EDITOR role tests
    $library->users()->updateExistingPivot($user->id, ['role' => LibraryRole::EDITOR]);
    $user->refresh();

    expect($user->hasLibraryRoleAtLeast($library, LibraryRole::VIEWER))->toBeTrue()
        ->and($user->hasLibraryRoleAtLeast($library, LibraryRole::EDITOR))->toBeTrue()
        ->and($user->hasLibraryRoleAtLeast($library, LibraryRole::OWNER))->toBeFalse();

    // OWNER role tests
    $library->users()->updateExistingPivot($user->id, ['role' => LibraryRole::OWNER]);
    $user->refresh();

    expect($user->hasLibraryRoleAtLeast($library, LibraryRole::VIEWER))->toBeTrue()
        ->and($user->hasLibraryRoleAtLeast($library, LibraryRole::EDITOR))->toBeTrue()
        ->and($user->hasLibraryRoleAtLeast($library, LibraryRole::OWNER))->toBeTrue();
});

test('LibraryPolicy correctly enforces view permissions', function () {
    $viewers = [];
    $nonMembers = [];
    $library = Library::factory()->create();

    // Create users with different roles
    $owner = User::factory()->create();
    $editor = User::factory()->create();
    $viewer = User::factory()->create();
    $nonMember = User::factory()->create();

    // Assign roles
    $library->users()->attach([
        $owner->id => ['role' => LibraryRole::OWNER],
        $editor->id => ['role' => LibraryRole::EDITOR],
        $viewer->id => ['role' => LibraryRole::VIEWER],
    ]);

    // Policy tests for view permission
    expect($owner->can('view', $library))->toBeTrue()
        ->and($editor->can('view', $library))->toBeTrue()
        ->and($viewer->can('view', $library))->toBeTrue()
        ->and($nonMember->can('view', $library))->toBeFalse();
});

test('LibraryPolicy correctly enforces update permissions', function () {
    $library = Library::factory()->create();

    // Create users with different roles
    $owner = User::factory()->create();
    $editor = User::factory()->create();
    $viewer = User::factory()->create();
    $nonMember = User::factory()->create();

    // Assign roles
    $library->users()->attach([
        $owner->id => ['role' => LibraryRole::OWNER],
        $editor->id => ['role' => LibraryRole::EDITOR],
        $viewer->id => ['role' => LibraryRole::VIEWER],
    ]);

    // Policy tests for update permission
    expect($owner->can('update', $library))->toBeTrue()
        ->and($editor->can('update', $library))->toBeFalse()
        ->and($viewer->can('update', $library))->toBeFalse()
        ->and($nonMember->can('update', $library))->toBeFalse();
});

test('LibraryPolicy correctly enforces delete permissions', function () {
    $library = Library::factory()->create();

    // Create users with different roles
    $owner = User::factory()->create();
    $editor = User::factory()->create();
    $viewer = User::factory()->create();
    $nonMember = User::factory()->create();

    // Assign roles
    $library->users()->attach([
        $owner->id => ['role' => LibraryRole::OWNER],
        $editor->id => ['role' => LibraryRole::EDITOR],
        $viewer->id => ['role' => LibraryRole::VIEWER],
    ]);

    // Policy tests for delete permission
    expect($owner->can('delete', $library))->toBeTrue()
        ->and($editor->can('delete', $library))->toBeFalse()
        ->and($viewer->can('delete', $library))->toBeFalse()
        ->and($nonMember->can('delete', $library))->toBeFalse();
});
