<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    //店舗一覧ページのテスト
    public function test_guest_can_using_restaurant_detail_page()
    {
        $response = $this->get('/restaurants');

        $response->assertStatus(200);
    }

    public function test_user_can_access_restaurant_detail_page()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/restaurants');
        $response->assertStatus(200);
    }

    public function test_admin_can_not_access_restaurant_detail_page() 
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->get('/restaurants');
        $response->assertRedirect(route('admin.home'));
    }

    //店舗詳細ページのテスト
    public function test_guest_can_using_restaurant_show()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.show', ['restaurant' => $restaurant->id] ));

        $response->assertStatus(200);
    }

    public function test_user_can_access_restaurant_show()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('restaurants.show', ['restaurant' => $restaurant->id]) );
        $response->assertStatus(200);
    }

    public function test_admin_can_not_access_restaurant_show() 
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('restaurants.show', ['restaurant' => $restaurant->id]) );
        $response->assertRedirect(route('admin.home'));
    }

}