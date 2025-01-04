<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\RegularHoliday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    // 店舗一覧ページのテスト
    public function test_guest_cannot_access_admin_restaurant_index()
    {
        $response = $this->get('/admin/restaurants');
        $response->assertRedirect('/admin/login');
    }

    public function test_regular_user_cannot_access_admin_restaurant_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.restaurants.index'));
        $response->assertRedirect('admin/login');
    }

    public function test_admin_can_access_admin_restaurant_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.restaurants.index'));
        $response->assertStatus(200);
    }

    // 店舗詳細ページのテスト
    public function test_guest_cannot_access_admin_restaurant_show()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('admin.restaurants.show', $restaurant));
        $response->assertRedirect('/admin/login');
    }

    public function test_regular_user_cannot_access_admin_restaurant_show()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.restaurants.show', $restaurant));
        $response->assertRedirect('admin/login');
    }

    public function test_admin_can_access_admin_restaurant_show()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.restaurants.show', $restaurant));

        $response->assertStatus(200);
    }

    // 店舗登録ページのテスト
    public function test_guest_cannot_access_admin_restaurant_create()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('admin.restaurants.create', $restaurant));
        $response->assertRedirect('admin/login');
    }

    public function test_regular_user_cannot_access_admin_restaurant_create()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.restaurants.create', $restaurant));
        $response->assertRedirect('admin/login');
    }

    public function test_admin_can_access_admin_restaurant_create()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.restaurants.create', $restaurant));
        $response->assertStatus(200);
    }

    // 店舗登録機能のテスト
    public function test_guest_cannot_store_restaurant()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->put(route('admin.restaurants.create', $restaurant), [
            'name' => 'Test Restaurant',
            'image' => UploadedFile::fake()->create('dummy.txt', 100, 'text/plain'),
        ]);

        $response->assertRedirect('admin/login');
    }

    public function test_regular_user_cannot_store_restaurant()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->put(route('admin.restaurants.create', $restaurant), [
            'name' => 'Test Restaurant',
            'image' => UploadedFile::fake()->create('dummy.txt', 100, 'text/plain'),
        ]);

        $response->assertRedirect('admin/login');
    }

    public function test_admin_can_store_restaurant()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/restaurants', [
            'name' => 'Test Restaurant',
            'image' => UploadedFile::fake()->image('dummy.jpg', 100, 100),
            'description' => 'Test description',
            'lowest_price' => 1000,
            'highest_price' => 2000,
            'postal_code' => '1234567',
            'address' => 'Test address',
            'opening_time' => '10:00',
            'closing_time' => '22:00',
            'seating_capacity' => 50,
            'category_ids' => [1, 2],
            'regular_holiday_ids' => [1],
        ]);

        /*$response->dump();*/
        $response->assertStatus(302);
    }

    // 店舗編集ページのテスト
    public function test_guest_cannot_access_admin_restaurant_edit()
    {
        $response = $this->get(route('admin.restaurants.edit', 1));

        $response->assertRedirect('admin/login');
    }

    public function test_regular_user_cannot_access_admin_restaurant_edit()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.restaurants.edit', $restaurant));
        $response->assertRedirect('admin/login');
    }

    public function test_admin_can_access_admin_restaurant_edit()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.restaurants.edit', $restaurant));
        $response->assertStatus(200);
    }

    // 店舗更新機能のテスト
    public function test_guest_cannot_update_restaurant()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->put(route('admin.restaurants.update', $restaurant), [
            'name' => 'Updated Restaurant',
            'image' => UploadedFile::fake()->image('dummy.jpg', 100, 100),
            'description' => 'Updated Description',
            'lowest_price' => 1000,
            'highest_price' => 2000,
            'postal_code' => '1234567',
            'address' => 'Updated Address',
            'opening_time' => '10:00',
            'closing_time' => '22:00',
            'seating_capacity' => 50,
            'category_ids' => [1, 2],
            'regular_holiday_ids' => [1],
        ]);

        $response->assertRedirect('admin/login');
    }

    public function test_regular_user_cannot_update_restaurant()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('admin.restaurants.update', $restaurant), [
                'name' => 'Updated Restaurant',
                'image' => UploadedFile::fake()->image('dummy.jpg', 100, 100),
                'description' => 'Updated Description',
                'lowest_price' => 1000,
                'highest_price' => 2000,
                'postal_code' => '1234567',
                'address' => 'Updated Address',
                'opening_time' => '10:00',
                'closing_time' => '22:00',
                'seating_capacity' => 50,
                'category_ids' => [1, 2],
                'regular_holiday_ids' => [1],
            ]);

        $response->assertRedirect('admin/login');
    }

    public function test_admin_can_update_restaurant()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        $restaurant = Restaurant::factory()->create();

        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $holiday = RegularHoliday::factory()->create();

        $this->actingAs($admin, 'admin');

        $updateData = [
            'name' => 'Updated Restaurant',
            'image' => UploadedFile::fake()->image('dummy.jpg', 100, 100),
            'description' => 'Updated Description',
            'lowest_price' => 1000,
            'highest_price' => 2000,
            'postal_code' => '1234567',
            'address' => 'Updated Address',
            'opening_time' => '10:00',
            'closing_time' => '22:00',
            'seating_capacity' => 50,
            'category_ids' => [$category1->id, $category2->id],
            'regular_holiday_ids' => [$holiday->id],
        ];

        $response = $this->put(route('admin.restaurants.update', $restaurant), $updateData);

        $response->assertRedirect(route('admin.restaurants.index'));

        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => 'Updated Restaurant',
            'description' => 'Updated Description',
            'lowest_price' => 1000,
            'highest_price' => 2000,
            'postal_code' => '1234567',
            'address' => 'Updated Address',
            'seating_capacity' => 50,
        ]);

        $this->assertNotNull($restaurant->fresh()->image);
    }

    // 店舗削除機能のテスト
    public function test_guest_cannot_delete_restaurant()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->delete(route('admin.restaurants.destroy', $restaurant));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_regular_user_cannot_delete_restaurant()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('admin.restaurants.destroy', $restaurant));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_delete_restaurant()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->delete(route('admin.restaurants.destroy', $restaurant));

        $response->assertRedirect(route('admin.restaurants.index'))
            ->assertSessionHas('flash_message', '店舗を削除しました。');

        $this->assertDatabaseMissing('restaurants', ['id' => $restaurant->id]);
    }
}
