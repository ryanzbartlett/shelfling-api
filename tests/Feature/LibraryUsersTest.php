<?php

use App\Enums\LibraryRole;
use App\Enums\LibraryType;
use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('users can retrieve library members with their roles', function () {
    // Create users
    $owner = User::factory()->create(['name' => 'Library Owner']);
    $editor = User::factory()->create(['name' => 'Library Editor']);
    $viewer = User::factory()->create(['name' => 'Library Viewer']);
    $nonMember = User::factory()->create(['name' => 'Non-Member User']);

    // Create a library
    $library = Library::factory()->create([
        'name' => 'Test Library',
        'type' => LibraryType::BOOK->value,
    ]);

    // Attach users with their roles
    $library->users()->attach([
        $owner->id => ['role' => LibraryRole::OWNER],
        $editor->id => ['role' => LibraryRole::EDITOR],
        $viewer->id => ['role' => LibraryRole::VIEWER],
    ]);

    // Library member can view users
    Sanctum::actingAs($viewer);

    $response = $this->getJson("/api/libraries/{$library->id}/users");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.name', $owner->name)
        ->assertJsonPath('data.0.role', LibraryRole::OWNER->value)
        ->assertJsonPath('data.1.name', $editor->name)
        ->assertJsonPath('data.1.role', LibraryRole::EDITOR->value)
        ->assertJsonPath('data.2.name', $viewer->name)
        ->assertJsonPath('data.2.role', LibraryRole::VIEWER->value);
});

test('non-members cannot view library users', function () {
    // Create users
    $owner = User::factory()->create();
    $nonMember = User::factory()->create();

    // Create a library
    $library = Library::factory()->create();

    // Attach only the owner
    $library->users()->attach($owner, ['role' => LibraryRole::OWNER]);

    // Non-member tries to view users
    Sanctum::actingAs($nonMember);

    $response = $this->getJson("/api/libraries/{$library->id}/users");

    $response->assertStatus(403);
});
