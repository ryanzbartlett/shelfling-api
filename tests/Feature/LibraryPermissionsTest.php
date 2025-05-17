<?php

use App\Enums\LibraryRole;
use App\Enums\LibraryType;
use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// Helper function to create a library with users in different roles
function createLibraryWithUsers()
{
    // Create users for each role
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

    return [
        'library' => $library,
        'owner' => $owner,
        'editor' => $editor,
        'viewer' => $viewer,
        'nonMember' => $nonMember,
    ];
}

test('all users can view their libraries listing', function () {
    $data = createLibraryWithUsers();
    
    // All authenticated users should be able to list libraries (viewAny permission)
    foreach ([$data['owner'], $data['editor'], $data['viewer'], $data['nonMember']] as $user) {
        Sanctum::actingAs($user);
        
        $this->getJson('/api/libraries')
            ->assertStatus(200);
    }
});

test('only library members can view specific library', function () {
    $data = createLibraryWithUsers();
    $library = $data['library'];
    
    // Members can view the library
    foreach ([$data['owner'], $data['editor'], $data['viewer']] as $user) {
        Sanctum::actingAs($user);
        
        $this->getJson("/api/libraries/{$library->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Test Library');
    }
    
    // Non-members cannot view the library
    Sanctum::actingAs($data['nonMember']);
    
    $this->getJson("/api/libraries/{$library->id}")
        ->assertStatus(403);
});

test('all authenticated users can create libraries', function () {
    $user = User::factory()->create();
    
    Sanctum::actingAs($user);
    
    $this->postJson('/api/libraries', [
        'name' => 'New Library',
        'type' => LibraryType::BOOK->value,
    ])
        ->assertStatus(201)
        ->assertJsonPath('data.name', 'New Library');
    
    // Verify the user is added as an owner to the new library
    $library = Library::where('name', 'New Library')->first();
    
    $this->assertDatabaseHas('library_user', [
        'library_id' => $library->id,
        'user_id' => $user->id,
        'role' => LibraryRole::OWNER->value,
    ]);
});

test('only library owners can update library details', function () {
    $data = createLibraryWithUsers();
    $library = $data['library'];
    
    // Owner can update the library
    Sanctum::actingAs($data['owner']);
    
    $this->putJson("/api/libraries/{$library->id}", [
        'name' => 'Updated Library Name',
        'type' => LibraryType::MOVIE->value,
    ])
        ->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Library Name');
    
    // Editor cannot update the library
    Sanctum::actingAs($data['editor']);
    
    $this->putJson("/api/libraries/{$library->id}", [
        'name' => 'Editor Changed Name',
        'type' => LibraryType::BOOK->value,
    ])
        ->assertStatus(403);
    
    // Viewer cannot update the library
    Sanctum::actingAs($data['viewer']);
    
    $this->putJson("/api/libraries/{$library->id}", [
        'name' => 'Viewer Changed Name',
        'type' => LibraryType::BOOK->value,
    ])
        ->assertStatus(403);
    
    // Non-member cannot update the library
    Sanctum::actingAs($data['nonMember']);
    
    $this->putJson("/api/libraries/{$library->id}", [
        'name' => 'Non-Member Changed Name',
        'type' => LibraryType::BOOK->value,
    ])
        ->assertStatus(403);
});

test('only library owners can delete libraries', function () {
    $data = createLibraryWithUsers();
    $library = $data['library'];
    
    // Editor cannot delete the library
    Sanctum::actingAs($data['editor']);
    
    $this->deleteJson("/api/libraries/{$library->id}")
        ->assertStatus(403);
    
    // Viewer cannot delete the library
    Sanctum::actingAs($data['viewer']);
    
    $this->deleteJson("/api/libraries/{$library->id}")
        ->assertStatus(403);
    
    // Non-member cannot delete the library
    Sanctum::actingAs($data['nonMember']);
    
    $this->deleteJson("/api/libraries/{$library->id}")
        ->assertStatus(403);
    
    // Owner can delete the library
    Sanctum::actingAs($data['owner']);
    
    $this->deleteJson("/api/libraries/{$library->id}")
        ->assertStatus(200);
    
    // Verify the library was deleted
    $this->assertDatabaseMissing('libraries', [
        'id' => $library->id,
    ]);
});

test('only library owners can add users to library', function () {
    $data = createLibraryWithUsers();
    $library = $data['library'];
    $newUser = User::factory()->create();
    
    $requestData = [
        'users' => [
            [
                'email' => $newUser->email,
                'role' => LibraryRole::VIEWER,
            ]
        ]
    ];
    
    // Editor cannot add users
    Sanctum::actingAs($data['editor']);
    
    $this->postJson("/api/libraries/{$library->id}/users", $requestData)
        ->assertStatus(403);
    
    // Viewer cannot add users
    Sanctum::actingAs($data['viewer']);
    
    $this->postJson("/api/libraries/{$library->id}/users", $requestData)
        ->assertStatus(403);
    
    // Non-member cannot add users
    Sanctum::actingAs($data['nonMember']);
    
    $this->postJson("/api/libraries/{$library->id}/users", $requestData)
        ->assertStatus(403);
    
    // Owner can add users
    Sanctum::actingAs($data['owner']);
    
    $this->postJson("/api/libraries/{$library->id}/users", $requestData)
        ->assertStatus(200);
    
    // Verify the user was added
    $this->assertDatabaseHas('library_user', [
        'library_id' => $library->id,
        'user_id' => $newUser->id,
        'role' => LibraryRole::VIEWER->value,
    ]);
});

test('only library owners can remove users from library', function () {
    $data = createLibraryWithUsers();
    $library = $data['library'];
    $viewerUserId = $data['viewer']->id;
    
    $requestData = [
        'user_id' => $viewerUserId
    ];
    
    // Editor cannot remove users
    Sanctum::actingAs($data['editor']);
    
    $this->deleteJson("/api/libraries/{$library->id}/users", $requestData)
        ->assertStatus(403);
    
    // Viewer cannot remove users
    Sanctum::actingAs($data['viewer']);
    
    $this->deleteJson("/api/libraries/{$library->id}/users", $requestData)
        ->assertStatus(403);
    
    // Non-member cannot remove users
    Sanctum::actingAs($data['nonMember']);
    
    $this->deleteJson("/api/libraries/{$library->id}/users", $requestData)
        ->assertStatus(403);
    
    // Owner can remove users
    Sanctum::actingAs($data['owner']);
    
    $this->deleteJson("/api/libraries/{$library->id}/users", $requestData)
        ->assertStatus(204);
    
    // Verify the user was removed
    $this->assertDatabaseMissing('library_user', [
        'library_id' => $library->id,
        'user_id' => $viewerUserId,
    ]);
});

test('owners cannot remove themselves from the library', function () {
    $data = createLibraryWithUsers();
    $library = $data['library'];
    $ownerId = $data['owner']->id;
    
    // Owner tries to remove themselves
    Sanctum::actingAs($data['owner']);
    
    $this->deleteJson("/api/libraries/{$library->id}/users", [
        'user_id' => $ownerId
    ])->assertStatus(403);
    
    // Verify the owner is still in the library
    $this->assertDatabaseHas('library_user', [
        'library_id' => $library->id,
        'user_id' => $ownerId,
        'role' => LibraryRole::OWNER->value,
    ]);
});