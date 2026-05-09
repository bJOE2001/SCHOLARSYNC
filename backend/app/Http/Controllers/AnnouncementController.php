<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NormalizesRequestInput;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnnouncementController extends Controller
{
    use NormalizesRequestInput;

    public function index(Request $request): AnonymousResourceCollection
    {
        $announcements = Announcement::query()
            ->when($request->filled('audience') && $request->audience !== 'All', fn ($query) => $query->where('audience', $request->audience))
            ->latest('publish_date')
            ->get();

        return AnnouncementResource::collection($announcements);
    }

    public function store(Request $request): JsonResponse
    {
        $announcement = Announcement::create($this->validateAnnouncement($request));

        return response()->json([
            'message' => 'Announcement published.',
            'data' => new AnnouncementResource($announcement),
        ], 201);
    }

    public function show(Announcement $announcement): AnnouncementResource
    {
        return new AnnouncementResource($announcement);
    }

    public function update(Request $request, Announcement $announcement): AnnouncementResource
    {
        $announcement->update($this->validateAnnouncement($request, updating: true));

        return new AnnouncementResource($announcement->refresh());
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted.',
        ]);
    }

    private function validateAnnouncement(Request $request, bool $updating = false): array
    {
        $payload = $this->normalizedInput($request, [
            'title' => 'title',
            'audience' => 'audience',
            'date' => 'publish_date',
            'publishDate' => 'publish_date',
            'publish_date' => 'publish_date',
            'message' => 'message',
        ]);

        $required = $updating ? 'sometimes' : 'required';

        $validated = validator($payload, [
            'title' => [$required, 'string', 'max:255'],
            'audience' => [$required, 'string', 'max:120'],
            'publish_date' => ['nullable', 'date'],
            'message' => [$required, 'string'],
        ])->validate();

        if (! $updating && empty($validated['publish_date'])) {
            $validated['publish_date'] = now()->toDateString();
        }

        return $validated;
    }
}
