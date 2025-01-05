<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Admin;
use App\Models\Reservation;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    //予約一覧ページのテスト
    public function test_guest_can_not_access_reservation_page()
    {
        $response = $this->get(route('reservations.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_not_reservation_page()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('reservations.index'));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_can_access_reservation_page()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);

        $this->actingAs($user);

        $response = $this->get(route('reservations.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_not_access_reservation_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('reservations.index'));
        $response->assertRedirect(route('admin.home'));
    }

    //予約ページのテスト
    public function test_guest_can_not_access_create_reservation_page()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reservations.create', $restaurant));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_not_access_create_reservation_page()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reservations.create', $restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_can_access_create_reservation_page()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reservations.create', $restaurant));
        $response->assertStatus(200);
    }

    public function test_admin_can_not_access_create_reservation_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('restaurants.reservations.create', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    //予約機能のテスト
    public function test_guest_can_not_reserve()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('restaurants.reservations.store', $restaurant), [
            'restaurant_id' => $restaurant->id,
            'reservation_date' => '2025-01-05',
            'reservation_time' => '11:00',
            'number_of_people' => 48
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_not_reserve()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('restaurants.reservations.store', $restaurant), [
            'restaurant_id' => $restaurant->id,
            'reservation_date' => '2025-01-05',
            'reservation_time' => '11:00',
            'number_of_people' => 12
        ]);

        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_can_reserve()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);


        $response = $this->post(route('restaurants.reservations.store', $restaurant), [
            'restaurant_id' => $restaurant->id,
            'reservation_date' => '2025-01-05',
            'reservation_time' => '11:00',
            'number_of_people' => 37
        ]);

        $response->assertRedirect(route('reservations.index'));
    }

    public function test_admin_can_not_reserve()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->post(route('restaurants.reservations.store', $restaurant), [
            'restaurant_id' => $restaurant->id,
            'reservation_date' => '2025-01-05',
            'reservation_time' => '11:00',
            'number_of_people' => 43
        ]);

        $response->assertRedirect(route('admin.home'));
    }

    // 予約キャンセル機能のテスト
    public function test_guest_cannot_delete_reservation()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();  
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id  
        ]);

        $response = $this->delete(route('reservations.destroy', $reservation));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_delete_reservation()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('reservations.destroy', $reservation));

        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_cannot_delete_others_reservation()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => User::factory()->create()->id
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('reservations.destroy', $reservation));

        $response->assertRedirect(route('reservations.index'));
        $response->assertSessionHas('error_message', '不正なアクセスです。');
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
    }

    public function test_premium_user_can_delete_own_reservation()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('reservations.destroy', $reservation));

        $response->assertRedirect(route('reservations.index'));
        $response->assertSessionHas('flash_message', '予約をキャンセルしました。');
        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }

    public function test_admin_cannot_delete_reservation()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();  
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id  
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->delete(route('reservations.destroy', $reservation));

        $response->assertRedirect(route('admin.home'));
    }
}
