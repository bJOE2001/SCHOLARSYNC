<?php

namespace App\Http\Controllers;

use App\Models\ScholarshipApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function applications(Request $request): JsonResponse
    {
        $rows = ScholarshipApplication::query()
            ->when(
                $request->filled('program') && $request->program !== 'All Programs',
                fn ($query) => $query->where('program', $request->program)
            )
            ->when(
                $request->filled('status') && ! in_array($request->status, ['All', 'All Statuses'], true),
                fn ($query) => $query->where('status', $request->status)
            )
            ->when($request->dateRange === 'This Month', fn ($query) => $query->whereBetween('date_submitted', [now()->startOfMonth(), now()->endOfMonth()]))
            ->when($request->dateRange === 'This Quarter', fn ($query) => $query->whereBetween('date_submitted', [now()->startOfQuarter(), now()->endOfQuarter()]))
            ->latest('date_submitted')
            ->get()
            ->map(fn (ScholarshipApplication $application) => [
                'applicantName' => $application->applicant_name,
                'program' => $application->program,
                'status' => $application->status,
                'submitted' => $application->date_submitted?->format('F j, Y'),
            ]);

        return response()->json([
            'rows' => $rows,
            'totals' => [
                'applications' => $rows->count(),
                'approved' => $rows->where('status', 'Approved')->count(),
                'pending' => $rows->whereIn('status', ['Pending', 'Under Review'])->count(),
            ],
        ]);
    }
}
