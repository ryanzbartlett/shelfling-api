<?php

use App\Enums\LibraryRole;
use App\Enums\LibraryType;
use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('library resource includes requesting user role', function () {
    // Create a user with viewer role
    $viewer = User::factory()->create(['name' => 'Library Viewer']);

    // Create a library
    $library = Library::factory()->create([
        'name' => 'Test Library',
        'type' => LibraryType::BOOK->value,
    ]);

    // Attach user as viewer
    $library->users()->attach($viewer, ['role' => LibraryRole::VIEWER]);

    // Make the request as the viewer
    Sanctum::actingAs($viewer);

    $response = $this->getJson("/api/libraries/{$library->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Test Library')
        ->assertJsonPath('data.role', LibraryRole::VIEWER->value);

    // Create a user with editor role
    $editor = User::factory()->create(['name' => 'Library Editor']);

    // Attach user as editor
    $library->users()->attach($editor, ['role' => LibraryRole::EDITOR]);

    // Make the request as the editor
    Sanctum::actingAs($editor);

    $response = $this->getJson("/api/libraries/{$library->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Test Library')
        ->assertJsonPath('data.role', LibraryRole::EDITOR->value);
});

test('library collection includes user role for each library', function () {
    // Create a user
    $user = User::factory()->create();

    // Create two libraries
    $library1 = Library::factory()->create(['name' => 'Library 1']);
    $library2 = Library::factory()->create(['name' => 'Library 2']);

    // Attach user with different roles to each library
    $library1->users()->attach($user, ['role' => LibraryRole::OWNER]);
    $library2->users()->attach($user, ['role' => LibraryRole::EDITOR]);

    // Make the request as the user
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/libraries");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');

    // Find the index of each library in the response
    $responseData = $response->json('data');
    $lib1Index = array_search('Library 1', array_column($responseData, 'name'));
    $lib2Index = array_search('Library 2', array_column($responseData, 'name'));

    // Assert the correct roles for each library
    $response->assertJsonPath("data.{$lib1Index}.role", LibraryRole::OWNER->value)
        ->assertJsonPath("data.{$lib2Index}.role", LibraryRole::EDITOR->value);
});
