<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([], 201);
    }

    public function login(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        // Attempt to authenticate the user using their email and password
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        // Retrieve the authenticated user
        $user = Auth::user();

        // Create a new personal access token for the user
        $token = $user->createToken('access_token')->plainTextToken;

        // Return the access token in the response
        return response()->json([
            'access_token' => $token,
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([], 204);
    }

    public function user(Request $request) {
        return new UserResource($request->user());
    }
}
