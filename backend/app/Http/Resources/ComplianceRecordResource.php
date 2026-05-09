<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplianceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scholarName' => $this->scholar_name,
            'name' => $this->scholar_name,
            'gpa' => $this->gpa,
            'complianceScore' => $this->compliance_score.'%',
            'complianceStatus' => $this->compliance_status,
            'riskLevel' => $this->risk_level,
            'gpaTrend' => $this->gpa_trend,
            'riskScore' => $this->risk_score,
            'forecastLabel' => $this->forecast_label,
        ];
    }
}
