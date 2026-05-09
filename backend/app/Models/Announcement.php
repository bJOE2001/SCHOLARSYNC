<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'audience',
        'publish_date',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
        ];
    }
}
