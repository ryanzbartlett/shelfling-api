<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\{getJson, postJson};

uses(RefreshDatabase::class);

test('tokens are created with the correct abilities', function () {
    $user = User::factory()->create();
    
    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    
    $response->assertStatus(200);
    
    // Check token was created
    $this->assertDatabaseCount('personal_access_tokens', 1);
    
    // Default tokens should have no specific abilities (full access)
    $token = $user->tokens()->first();
    $this->assertEquals(['*'], $token->abilities);
});

test('authenticated user can access protected routes with valid token', function () {
    $user = User::factory()->create();
    
    Sanctum::actingAs($user);
    
    // Now we should be able to access protected routes
    getJson('/api/user')
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    
    // Check we can access our libraries
    getJson('/api/libraries')
        ->assertStatus(200);
});

test('expired tokens are rejected', function () {
    // This test simulates an expired token by manipulating the token's expiration
    
    $user = User::factory()->create();
    
    // Create a token that has already expired
    $token = $user->createToken('test-token');
    
    // Manually expire the token in the database
    $user->tokens()->update([
        'created_at' => now()->subDays(8), // Default expiry is 7 days
    ]);
    
    // Attempt to use the token
    getJson('/api/user', [
        'Authorization' => 'Bearer ' . $token->plainTextToken,
    ])->assertStatus(401);
});

test('all tokens for a user are deleted on logout', function () {
    $user = User::factory()->create();
    
    // Create multiple tokens
    $token1 = $user->createToken('token-1')->plainTextToken;
    $token2 = $user->createToken('token-2')->plainTextToken;
    $token3 = $user->createToken('token-3')->plainTextToken;
    
    $this->assertDatabaseCount('personal_access_tokens', 3);
    
    // Logout with one of the tokens
    getJson('/api/logout', [
        'Authorization' => 'Bearer ' . $token1,
    ])->assertStatus(204);
    
    // All tokens should be deleted
    $this->assertDatabaseCount('personal_access_tokens', 0);
});