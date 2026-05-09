<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait NormalizesRequestInput
{
    protected function normalizedInput(Request $request, array $map): array
    {
        $payload = [];

        foreach ($map as $inputKey => $databaseKey) {
            if ($request->has($inputKey)) {
                $payload[$databaseKey] = $request->input($inputKey);
            }
        }

        return $payload;
    }
}
