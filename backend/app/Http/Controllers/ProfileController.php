<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileController extends Controller
{
    public function student(Request $request): JsonResource
    {
        $student = User::query()
            ->with(['applications' => fn ($query) => $query->latest()])
            ->where('role', 'student')
            ->when($request->filled('user_id'), fn ($query) => $query->where('id', $request->integer('user_id')))
            ->firstOrFail();

        return new UserProfileResource($student);
    }
}
