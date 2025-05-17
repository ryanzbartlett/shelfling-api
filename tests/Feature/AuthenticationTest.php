<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\{postJson, getJson};

uses(RefreshDatabase::class);

test('users can register with valid credentials', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
    ];

    postJson('/api/register', $userData)
        ->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('users cannot register with invalid data', function () {
    // Missing name
    postJson('/api/register', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ])->assertStatus(422);

    // Invalid email
    postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password123',
    ])->assertStatus(422);

    // Short password
    postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'short',
    ])->assertStatus(422);

    // Duplicate email
    User::factory()->create(['email' => 'existing@example.com']);

    postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password123',
    ])->assertStatus(422);
});

test('users can login with correct credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'expires_at',
        ]);
});

test('users cannot login with incorrect credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // Wrong password
    postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(401);

    // Wrong email
    postJson('/api/login', [
        'email' => 'wrong@example.com',
        'password' => 'password123',
    ])->assertStatus(401);
});

test('users can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    getJson('/api/logout', [
        'Authorization' => 'Bearer ' . $token,
    ])->assertStatus(204);

    // Verify token was deleted
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('users can retrieve their profile when authenticated', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = getJson('/api/user', [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
});

test('unauthenticated users cannot access protected routes', function () {
    // Without token
    getJson('/api/user')->assertStatus(401);
    getJson('/api/logout')->assertStatus(401);
    getJson('/api/libraries')->assertStatus(401);
});
