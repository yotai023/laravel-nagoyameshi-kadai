<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $restaurant;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'is_admin' => true
        ]);
        
        // Create regular user
        $this->user = User::factory()->create([
            'is_admin' => false
        ]);
        
        // Create test restaurant
        $this->restaurant = Restaurant::factory()->create();
    }

    // Index Action Tests
    public function test_guest_cannot_access_admin_restaurant_index()
    {
        $response = $this->get(route('admin.restaurants.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_admin_restaurant_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.restaurants.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_restaurant_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.restaurants.index'));
        $response->assertOk();
    }

    // Show Action Tests
    public function test_guest_cannot_access_admin_restaurant_show()
    {
        $response = $this->get(route('admin.restaurants.show', $this->restaurant));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_admin_restaurant_show()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.restaurants.show', $this->restaurant));
        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_restaurant_show()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.restaurants.show', $this->restaurant));
        $response->assertOk();
    }

    // Create Action Tests
    public function test_guest_cannot_access_admin_restaurant_create()
    {
        $response = $this->get(route('admin.restaurants.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_admin_restaurant_create()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.restaurants.create'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_restaurant_create()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.restaurants.create'));
        $response->assertOk();
    }

    // Store Action Tests
    public function test_guest_cannot_store_restaurant()
    {
        $response = $this->post(route('admin.restaurants.store'), [
            'name' => 'Test Restaurant',
            'image' => UploadedFile::fake()->image('test.jpg'),
            'description' => 'Test Description',
            'lowest_price' => 1000,
            'highest_price' => 5000,
            'postal_code' => '1234567',
            'address' => 'Test Address',
            'opening_time' => '10:00:00',
            'closing_time' => '22:00:00',
            'seating_capacity' => 50
        ]);
        
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_store_restaurant()
    {
        $response = $this->actingAs($this->user)
            ->post(route('admin.restaurants.store'), [
                'name' => 'Test Restaurant',
                'image' => UploadedFile::fake()->image('test.jpg'),
                'description' => 'Test Description',
                'lowest_price' => 1000,
                'highest_price' => 5000,
                'postal_code' => '1234567',
                'address' => 'Test Address',
                'opening_time' => '10:00:00',
                'closing_time' => '22:00:00',
                'seating_capacity' => 50
            ]);
            
        $response->assertForbidden();
    }

    public function test_admin_can_store_restaurant()
    {
        Storage::fake('public');
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.restaurants.store'), [
                'name' => 'Test Restaurant',
                'image' => UploadedFile::fake()->image('test.jpg'),
                'description' => 'Test Description',
                'lowest_price' => 1000,
                'highest_price' => 5000,
                'postal_code' => '1234567',
                'address' => 'Test Address',
                'opening_time' => '10:00:00',
                'closing_time' => '22:00:00',
                'seating_capacity' => 50
            ]);
            
        $response->assertRedirect(route('admin.restaurants.index'));
        $this->assertDatabaseHas('restaurants', ['name' => 'Test Restaurant']);
    }

    // Edit Action Tests
    public function test_guest_cannot_access_admin_restaurant_edit()
    {
        $response = $this->get(route('admin.restaurants.edit', $this->restaurant));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_admin_restaurant_edit()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.restaurants.edit', $this->restaurant));
        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_restaurant_edit()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.restaurants.edit', $this->restaurant));
        $response->assertOk();
    }

    // Update Action Tests
    public function test_guest_cannot_update_restaurant()
    {
        $response = $this->put(route('admin.restaurants.update', $this->restaurant), [
            'name' => 'Updated Restaurant',
            'image' => UploadedFile::fake()->image('test.jpg'),
            'description' => 'Updated Description',
            'lowest_price' => 2000,
            'highest_price' => 6000,
            'postal_code' => '1234567',
            'address' => 'Updated Address',
            'opening_time' => '09:00:00',
            'closing_time' => '21:00:00',
            'seating_capacity' => 60
        ]);
        
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_update_restaurant()
    {
        $response = $this->actingAs($this->user)
            ->put(route('admin.restaurants.update', $this->restaurant), [
                'name' => 'Updated Restaurant',
                'image' => UploadedFile::fake()->image('test.jpg'),
                'description' => 'Updated Description',
                'lowest_price' => 2000,
                'highest_price' => 6000,
                'postal_code' => '1234567',
                'address' => 'Updated Address',
                'opening_time' => '09:00:00',
                'closing_time' => '21:00:00',
                'seating_capacity' => 60
            ]);
            
        $response->assertForbidden();
    }

    public function test_admin_can_update_restaurant()
    {
        Storage::fake('public');
        
        $response = $this->actingAs($this->admin)
            ->put(route('admin.restaurants.update', $this->restaurant), [
                'name' => 'Updated Restaurant',
                'image' => UploadedFile::fake()->image('test.jpg'),
                'description' => 'Updated Description',
                'lowest_price' => 2000,
                'highest_price' => 6000,
                'postal_code' => '1234567',
                'address' => 'Updated Address',
                'opening_time' => '09:00:00',
                'closing_time' => '21:00:00',
                'seating_capacity' => 60
            ]);
            
        $response->assertRedirect(route('admin.restaurants.index'));
        $this->assertDatabaseHas('restaurants', ['name' => 'Updated Restaurant']);
    }

    // Destroy Action Tests
    public function test_guest_cannot_delete_restaurant()
    {
        $response = $this->delete(route('admin.restaurants.destroy', $this->restaurant));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_delete_restaurant()
    {
        $response = $this->actingAs($this->user)
            ->delete(route('admin.restaurants.destroy', $this->restaurant));
        $response->assertForbidden();
    }

    public function test_admin_can_delete_restaurant()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.restaurants.destroy', $this->restaurant));
            
        $response->assertRedirect(route('admin.restaurants.show'));
        $this->assertDatabaseMissing('restaurants', ['id' => $this->restaurant->id]);
    }
}