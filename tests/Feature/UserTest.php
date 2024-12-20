<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_using_restaurant_page(): void
    {
        $response = $this->get('/restaurants');

        $response->assertStatus(200);
    }
}
