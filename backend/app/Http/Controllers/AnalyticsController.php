<?php

namespace App\Http\Controllers;

use App\Http\Resources\ComplianceRecordResource;
use App\Models\ComplianceRecord;
use App\Models\ScholarshipApplication;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function index(): JsonResponse
    {
        $applicantCount = ScholarshipApplication::count();
        $approvedCount = ScholarshipApplication::where('status', 'Approved')->count();
        $approvalProbability = $applicantCount > 0 ? round(($approvedCount / $applicantCount) * 100) : 0;
        $atRiskCount = ComplianceRecord::where('risk_level', 'High')->count();

        return response()->json([
            'cards' => [
                [
                    'title' => 'Forecasted Applicants',
                    'value' => number_format(max($applicantCount, 1) * 12),
                    'subtitle' => 'Expected next cycle',
                    'tone' => 'blue',
                ],
                [
                    'title' => 'Approval Probability',
                    'value' => $approvalProbability.'%',
                    'subtitle' => 'Based on current eligibility',
                    'tone' => 'indigo',
                ],
                [
                    'title' => 'At-Risk Scholars',
                    'value' => number_format($atRiskCount),
                    'subtitle' => 'Require intervention',
                    'tone' => 'red',
                ],
                [
                    'title' => 'Compliance Risk Overview',
                    'value' => $atRiskCount > 0 ? 'Medium' : 'Low',
                    'subtitle' => 'Campus-wide trend',
                    'tone' => 'amber',
                ],
            ],
            'applicantForecast' => [
                ['month' => 'Jun', 'applicants' => 980, 'approvals' => 516, 'probability' => 53],
                ['month' => 'Jul', 'applicants' => 1120, 'approvals' => 638, 'probability' => 57],
                ['month' => 'Aug', 'applicants' => 1080, 'approvals' => 626, 'probability' => 58],
                ['month' => 'Sep', 'applicants' => 1310, 'approvals' => 786, 'probability' => 60],
                ['month' => 'Oct', 'applicants' => 1240, 'approvals' => 719, 'probability' => 58],
                ['month' => 'Nov', 'applicants' => 1520, 'approvals' => 897, 'probability' => 59],
            ],
            'riskOverview' => [
                ['level' => 'Low', 'count' => ComplianceRecord::where('risk_level', 'Low')->count(), 'color' => '#2563eb'],
                ['level' => 'Medium', 'count' => ComplianceRecord::where('risk_level', 'Medium')->count(), 'color' => '#4f46e5'],
                ['level' => 'High', 'count' => ComplianceRecord::where('risk_level', 'High')->count(), 'color' => '#e11d48'],
            ],
            'predictedAtRiskScholars' => ComplianceRecordResource::collection(
                ComplianceRecord::whereIn('risk_level', ['Medium', 'High'])
                    ->orderByDesc('risk_score')
                    ->get()
            ),
        ]);
    }
}
