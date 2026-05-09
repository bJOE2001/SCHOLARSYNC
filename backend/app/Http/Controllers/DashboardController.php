<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\ComplianceRecordResource;
use App\Http\Resources\ScholarshipApplicationResource;
use App\Http\Resources\UserProfileResource;
use App\Models\Announcement;
use App\Models\ComplianceRecord;
use App\Models\Document;
use App\Models\ScholarshipApplication;
use App\Models\StudentNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function student(Request $request): JsonResponse
    {
        $student = User::query()
            ->with(['applications' => fn ($query) => $query->with('documents')->latest('date_submitted')])
            ->where('role', 'student')
            ->when($request->filled('user_id'), fn ($query) => $query->where('id', $request->integer('user_id')))
            ->firstOrFail();

        $application = $student->applications->first();
        $documentCount = Document::where('user_id', $student->id)->count();
        $verifiedCount = Document::where('user_id', $student->id)->where('verification_status', 'Verified')->count();
        $latestAnnouncement = Announcement::latest('publish_date')->first();
        $compliance = ComplianceRecord::where('user_id', $student->id)->first();

        return response()->json([
            'profile' => new UserProfileResource($student),
            'stats' => [
                [
                    'title' => 'Application Status',
                    'value' => $application?->status ?? 'Not Started',
                    'subtitle' => $application ? 'Last updated '.$application->updated_at->diffForHumans() : 'No application submitted',
                    'tone' => 'blue',
                ],
                [
                    'title' => 'Submitted Documents',
                    'value' => $documentCount.' of '.max($documentCount, 5),
                    'subtitle' => $verifiedCount.' verified',
                    'tone' => 'indigo',
                ],
                [
                    'title' => 'Compliance Status',
                    'value' => $compliance?->compliance_status ?? 'No Record',
                    'subtitle' => $compliance ? $compliance->risk_level.' risk' : 'No active requirements',
                    'tone' => $compliance?->risk_level === 'High' ? 'red' : 'green',
                ],
                [
                    'title' => 'Latest Announcement',
                    'value' => $latestAnnouncement?->title ?? 'No Updates',
                    'subtitle' => $latestAnnouncement?->publish_date?->format('F j, Y') ?? 'Check back later',
                    'tone' => 'amber',
                ],
            ],
            'currentApplication' => $application ? new ScholarshipApplicationResource($application) : null,
            'progressSteps' => ['Submitted', 'Under Review', 'Approved / Rejected'],
            'recentNotifications' => StudentNotification::query()
                ->where('audience', 'student')
                ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $student->id))
                ->latest('delivered_at')
                ->limit(5)
                ->get()
                ->map(fn (StudentNotification $notification) => [
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'time' => $notification->delivered_at?->diffForHumans() ?? $notification->created_at->diffForHumans(),
                ]),
            'announcements' => AnnouncementResource::collection(Announcement::latest('publish_date')->limit(5)->get()),
        ]);
    }

    public function admin(): JsonResponse
    {
        $totalApplicants = ScholarshipApplication::count();
        $pendingApplications = ScholarshipApplication::whereIn('status', ['Pending', 'Under Review', 'For Revision'])->count();
        $approvedScholars = ScholarshipApplication::where('status', 'Approved')->count();
        $atRiskScholars = ComplianceRecord::where('risk_level', 'High')->count();

        return response()->json([
            'stats' => [
                [
                    'title' => 'Total Applicants',
                    'value' => number_format($totalApplicants),
                    'subtitle' => 'All submitted applications',
                    'tone' => 'blue',
                ],
                [
                    'title' => 'Pending Applications',
                    'value' => number_format($pendingApplications),
                    'subtitle' => 'Needs review',
                    'tone' => 'amber',
                ],
                [
                    'title' => 'Approved Scholars',
                    'value' => number_format($approvedScholars),
                    'subtitle' => 'Active scholarship grants',
                    'tone' => 'green',
                ],
                [
                    'title' => 'At-Risk Scholars',
                    'value' => number_format($atRiskScholars),
                    'subtitle' => 'Flagged by monitoring',
                    'tone' => 'red',
                ],
            ],
            'recentApplications' => ScholarshipApplicationResource::collection(
                ScholarshipApplication::with('documents')->latest('updated_at')->limit(5)->get()
            ),
            'complianceRecords' => ComplianceRecordResource::collection(
                ComplianceRecord::latest('updated_at')->limit(5)->get()
            ),
            'recentNotifications' => StudentNotification::query()
                ->where('audience', 'admin')
                ->latest('delivered_at')
                ->limit(10)
                ->get()
                ->map(fn (StudentNotification $notification) => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'time' => $notification->delivered_at?->diffForHumans() ?? $notification->created_at->diffForHumans(),
                ]),
        ]);
    }
}
