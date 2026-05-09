<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasPrefixedId
{
    protected static function bootHasPrefixedId(): void
    {
        static::creating(function ($model): void {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = static::idPrefix().Str::lower(Str::random(8));
            }
        });
    }
}
