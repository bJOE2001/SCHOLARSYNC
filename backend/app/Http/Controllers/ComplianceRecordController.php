<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NormalizesRequestInput;
use App\Http\Resources\ComplianceRecordResource;
use App\Models\ComplianceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ComplianceRecordController extends Controller
{
    use NormalizesRequestInput;

    public function index(Request $request): AnonymousResourceCollection
    {
        $records = ComplianceRecord::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->lower()->toString();
                $query->whereRaw('LOWER(scholar_name) LIKE ?', ["%{$search}%"]);
            })
            ->when(
                $request->filled('status') && $request->status !== 'All',
                fn ($query) => $query->where('compliance_status', $request->status)
            )
            ->when(
                $request->filled('risk') && $request->risk !== 'All',
                fn ($query) => $query->where('risk_level', $request->risk)
            )
            ->orderBy('scholar_name')
            ->get();

        return ComplianceRecordResource::collection($records);
    }

    public function store(Request $request): JsonResponse
    {
        $record = ComplianceRecord::create($this->validateRecord($request));

        return response()->json([
            'message' => 'Compliance record saved.',
            'data' => new ComplianceRecordResource($record),
        ], 201);
    }

    public function update(Request $request, ComplianceRecord $complianceRecord): ComplianceRecordResource
    {
        $complianceRecord->update($this->validateRecord($request, updating: true));

        return new ComplianceRecordResource($complianceRecord->refresh());
    }

    private function validateRecord(Request $request, bool $updating = false): array
    {
        $payload = $this->normalizedInput($request, [
            'userId' => 'user_id',
            'user_id' => 'user_id',
            'scholarName' => 'scholar_name',
            'scholar_name' => 'scholar_name',
            'gpa' => 'gpa',
            'complianceScore' => 'compliance_score',
            'compliance_score' => 'compliance_score',
            'complianceStatus' => 'compliance_status',
            'compliance_status' => 'compliance_status',
            'riskLevel' => 'risk_level',
            'risk_level' => 'risk_level',
            'gpaTrend' => 'gpa_trend',
            'gpa_trend' => 'gpa_trend',
            'riskScore' => 'risk_score',
            'risk_score' => 'risk_score',
            'forecastLabel' => 'forecast_label',
            'forecast_label' => 'forecast_label',
        ]);

        $required = $updating ? 'sometimes' : 'required';

        return validator($payload, [
            'user_id' => ['nullable', 'exists:users,id'],
            'scholar_name' => [$required, 'string', 'max:255'],
            'gpa' => [$required, 'numeric', 'min:0', 'max:5'],
            'compliance_score' => [$required, 'integer', 'min:0', 'max:100'],
            'compliance_status' => [$required, Rule::in(['Compliant', 'Needs Monitoring', 'At Risk'])],
            'risk_level' => [$required, Rule::in(['Low', 'Medium', 'High'])],
            'gpa_trend' => ['nullable', 'string', 'max:120'],
            'risk_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'forecast_label' => ['nullable', 'string', 'max:120'],
        ])->validate();
    }
}
