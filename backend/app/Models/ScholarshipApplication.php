<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScholarshipApplication extends Model
{
    use HasFactory, HasPrefixedId;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'scholarship_id',
        'applicant_name',
        'email',
        'phone',
        'program',
        'course',
        'year_level',
        'gpa',
        'address',
        'reason',
        'date_submitted',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date_submitted' => 'date',
            'gpa' => 'decimal:2',
        ];
    }

    protected static function idPrefix(): string
    {
        return 'app-';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'scholarship_application_id');
    }
}
