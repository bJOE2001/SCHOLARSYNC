<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory, HasPrefixedId;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'scholarship_application_id',
        'student_name',
        'program',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'upload_date',
        'verification_status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'upload_date' => 'date',
        ];
    }

    protected static function idPrefix(): string
    {
        return 'doc-';
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ScholarshipApplication::class, 'scholarship_application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
