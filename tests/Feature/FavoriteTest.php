<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Restaurant;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    // お気に入り一覧ページのテスト
    public function test_guest_cannot_access_favorite_restaurants_index()
    {
        $response = $this->get(route('favorites.index'));
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_favorite_restaurants_index()
    {
        $user = User::factory()->create([
            'stripe_id' => null
        ]);

        $response = $this->actingAs($user)->get(route('favorites.index'));
        $response->assertRedirect('subscription/create');
    }

    public function test_premium_user_can_access_favorite_restaurants_index()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);

        $response = $this->actingAs($user)->get(route('favorites.index'));
        $response->assertOk();
        $response->assertViewIs('favorites.index');
    }

    public function test_admin_cannot_access_favorite_restaurants_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('favorites.index'));
        $response->assertRedirect('/admin/home');
    }

    // おお気に入り追加機能のテスト
    public function test_guest_cannot_add_favorite()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('favorites.store', $restaurant->id));
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_add_favorite()
    {
        $user = User::factory()->create([
            'stripe_id' => null
        ]);
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->post(route('favorites.store', $restaurant->id));
        $response->assertRedirect('subscription/create');
    }

    public function test_premium_user_can_add_favorite()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)
            ->from('/restaurants')
            ->post(route('favorites.store', $restaurant));

        $response->assertStatus(302);
    }

    public function test_admin_cannot_add_favorite()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post(route('favorites.store', $restaurant));
        $response->assertRedirect('/admin/home');
    }

    // お気に入り解除機能のテスト
    public function test_guest_cannot_remove_favorite()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('favorites.destroy', $restaurant->id));
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_remove_favorite()
    {
        $user = User::factory()->create([
            'stripe_id' => null
        ]);
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->post(route('favorites.destroy', $restaurant->id));
        $response->assertRedirect('subscription/create');
    }

    public function test_premium_user_can_remove_favorite()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->post(route('favorites.destroy', $restaurant));

        $response->assertRedirect();
    }

    public function test_admin_cannot_remove_favorite()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post(route('favorites.destroy', $restaurant));
        $response->assertRedirect('/admin/home');
    }
}