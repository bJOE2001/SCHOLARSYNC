<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $payload = [
            'first_name' => $request->input('firstName', $request->input('first_name')),
            'last_name' => $request->input('lastName', $request->input('last_name')),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'program' => $request->input('program'),
            'year_level' => $request->input('yearLevel', $request->input('year_level')),
            'address' => $request->input('address'),
            'role' => $request->input('role', 'student'),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('confirmPassword', $request->input('password_confirmation')),
        ];

        $validated = Validator::make($payload, [
            'first_name' => ['nullable', 'string', 'max:120', 'required_without:name'],
            'last_name' => ['nullable', 'string', 'max:120', 'required_without:name'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'program' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'role' => ['required', Rule::in(['student', 'administrator'])],
            'password' => ['required', 'confirmed', 'min:8'],
        ])->validate();

        $name = $validated['name'] ?? trim($validated['first_name'].' '.$validated['last_name']);

        $user = User::create([
            'name' => $name,
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'program' => $validated['program'] ?? null,
            'year_level' => $validated['year_level'] ?? null,
            'address' => $validated['address'] ?? null,
            'api_token' => hash('sha256', Str::random(80)),
        ]);

        return response()->json([
            'message' => 'Registration successful.',
            'token' => $user->api_token,
            'user' => new UserProfileResource($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['nullable', Rule::in(['student', 'administrator'])],
        ]);

        $query = User::query()->where('email', $validated['email']);

        if (! empty($validated['role'])) {
            $query->where('role', $validated['role']);
        }

        $user = $query->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid login credentials.',
            ], 422);
        }

        $user->forceFill([
            'api_token' => hash('sha256', Str::random(80)),
        ])->save();

        return response()->json([
            'message' => 'Login successful.',
            'token' => $user->api_token,
            'user' => new UserProfileResource($user->load(['applications' => fn ($query) => $query->latest()])),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if ($token) {
            User::where('api_token', $token)->update(['api_token' => null]);
        }

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }
}
