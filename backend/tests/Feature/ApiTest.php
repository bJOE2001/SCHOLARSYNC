<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiTest extends TestCase
{
    public function test_api_endpoint_returns_a_healthy_response(): void
    {
        $response = $this->getJson('/api/test');

        $response
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'message' => 'ScholarSync API is running.',
            ]);
    }
}
