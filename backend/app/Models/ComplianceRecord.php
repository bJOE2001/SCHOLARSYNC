<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scholar_name',
        'gpa',
        'compliance_score',
        'compliance_status',
        'risk_level',
        'gpa_trend',
        'risk_score',
        'forecast_label',
    ];

    protected function casts(): array
    {
        return [
            'gpa' => 'decimal:2',
            'compliance_score' => 'integer',
            'risk_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
