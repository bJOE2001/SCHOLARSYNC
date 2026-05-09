<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplianceRecordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScholarshipApplicationController;
use App\Http\Controllers\ScholarshipController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'ScholarSync API is running.',
    ]);
});

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/profile/student', [ProfileController::class, 'student']);

Route::get('/dashboard/student', [DashboardController::class, 'student']);
Route::get('/dashboard/admin', [DashboardController::class, 'admin']);
Route::get('/notifications', [NotificationController::class, 'index']);

Route::apiResource('scholarships', ScholarshipController::class);

Route::patch('/applications/{application}/status', [ScholarshipApplicationController::class, 'updateStatus']);
Route::apiResource('applications', ScholarshipApplicationController::class);

Route::patch('/documents/{document}/status', [DocumentController::class, 'updateStatus']);
Route::apiResource('documents', DocumentController::class);

Route::apiResource('announcements', AnnouncementController::class);

Route::get('/compliance-records', [ComplianceRecordController::class, 'index']);
Route::post('/compliance-records', [ComplianceRecordController::class, 'store']);
Route::patch('/compliance-records/{complianceRecord}', [ComplianceRecordController::class, 'update']);

Route::get('/analytics', [AnalyticsController::class, 'index']);
Route::get('/reports/applications', [ReportController::class, 'applications']);
