<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scholarship extends Model
{
    use HasFactory, HasPrefixedId;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'scholarship_name',
        'description',
        'eligibility_requirements',
        'required_documents',
        'deadline',
        'available_slots',
        'announcement_details',
        'status',
        'scholarship_type',
        'academic_year',
        'semester',
        'minimum_gpa',
        'year_level_allowed',
        'program_allowed',
        'contact_person',
        'date_posted',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'date_posted' => 'date',
            'available_slots' => 'integer',
            'minimum_gpa' => 'decimal:2',
        ];
    }

    protected static function idPrefix(): string
    {
        return 'sch-';
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ScholarshipApplication::class);
    }
}
