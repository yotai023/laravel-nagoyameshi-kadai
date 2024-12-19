<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_using_restaurant_detail_page(): void
    {
        $response = $this->get('/restaurants');

        $response->assertStatus(200);
    }

    public function test_user_can_access_restaurant_detail_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/restaurants');
        $response->assertStatus(200);
    }

    public function test_admin_can_not_access_restaurant_detail_page(): void 
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin);

        $response = $this->get('/restaurants');
        $response->assertStatus(403);
    }
}
