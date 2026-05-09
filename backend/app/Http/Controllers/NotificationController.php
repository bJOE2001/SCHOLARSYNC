<?php

namespace App\Http\Controllers;

use App\Models\StudentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'audience' => ['nullable', Rule::in(['admin', 'student'])],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $audience = $validated['audience'] ?? 'student';

        $notifications = StudentNotification::query()
            ->where('audience', $audience)
            ->when($audience === 'student', function ($query) use ($validated): void {
                $query->where(function ($query) use ($validated): void {
                    $query->whereNull('user_id');

                    if (! empty($validated['user_id'])) {
                        $query->orWhere('user_id', $validated['user_id']);
                    }
                });
            })
            ->latest('delivered_at')
            ->limit(10)
            ->get()
            ->map(fn (StudentNotification $notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'time' => $notification->delivered_at?->diffForHumans() ?? $notification->created_at->diffForHumans(),
                'audience' => $notification->audience,
                'deliveredAt' => $notification->delivered_at?->toIso8601String(),
            ]);

        return response()->json([
            'data' => $notifications,
        ]);
    }
}
