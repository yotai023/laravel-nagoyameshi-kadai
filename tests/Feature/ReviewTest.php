<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Restaurant;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;


class ReviewTest extends TestCase
{
    use RefreshDatabase;

    //レビュー一覧のテスト
    public function test_guest_can_access_review_page()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reviews.index', $restaurant));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_access_review_page()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reviews.index', $restaurant));
        $response->assertStatus(200);
    }

    public function test_premium_user_can_access_review_page()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reviews.index', $restaurant));
        $response->assertStatus(200);
    }

    public function test_admin_can_not_access_restaurant_detail_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('restaurants.reviews.index', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    //レビュー投稿ページのテスト
    public function test_guest_can_access_review_submit_page()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reviews.create', $restaurant));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_not_access_review_submit_page()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reviews.create', $restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_can_access_review_submit_page()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reviews.create', $restaurant));
        $response->assertStatus(200);
    }

    public function test_admin_can_not_access_review_submit_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('restaurants.reviews.create', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    //レビュー投稿機能のテスト
    public function test_guest_can_not_submit_review()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('restaurants.reviews.store', $restaurant));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_not_submit_review()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('restaurants.reviews.store', $restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_can_submit_review()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $formData = [
            'score' => 5,
            'content' => 'Great restaurant!'
        ];

        $response = $this->post(route('restaurants.reviews.store', $restaurant), $formData);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    public function test_admin_can_not_submit_review()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin');

        $formData = [
            'score' => 5,
            'content' => 'Admin review'
        ];

        $response = $this->post(route('restaurants.reviews.store', $restaurant), $formData);
        $response->assertRedirect(route('admin.home'));
    }

    // レビュー編集ページのテスト
    public function test_guest_cannot_access_review_edit_page()
    {
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => User::factory()->create()->id
        ]);

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_review_edit_page()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => User::factory()->create()->id
        ]);

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    public function test_premium_user_cannot_access_others_review_edit_page()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $otherUser = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $otherUser->id
        ]);

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    public function test_premium_user_can_access_own_review_edit_page()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_review_edit_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => User::factory()->create()->id
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('admin.home'));
    }

    // レビュー更新機能のテスト
    public function test_guest_cannot_update_review()
    {
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id
        ]);

        $validData = [
            'score' => 4,
            'content' => '更新されたレビュー内容'
        ];

        $response = $this->put(route('restaurants.reviews.update', [
            'restaurant' => $restaurant,
            'review' => $review
        ]), $validData);

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_update_review()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $validData = [
            'score' => 4,
            'content' => '更新されたレビュー内容'
        ];

        $response = $this->put(route('restaurants.reviews.update', [
            'restaurant' => $restaurant,
            'review' => $review
        ]), $validData);

        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_cannot_update_others_review()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $otherUser = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $otherUser->id
        ]);

        $this->actingAs($user);

        $validData = [
            'score' => 4,
            'content' => '更新されたレビュー内容'
        ];

        $response = $this->put(route('restaurants.reviews.update', [
            'restaurant' => $restaurant,
            'review' => $review
        ]), $validData);

        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
        $response->assertSessionHas('error_message', '不正なアクセスです。');
    }

    public function test_premium_user_can_update_own_review()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $validData = [
            'score' => 4,
            'content' => '更新されたレビュー内容'
        ];

        $response = $this->put(route('restaurants.reviews.update', [
            'restaurant' => $restaurant,
            'review' => $review
        ]), $validData);

        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
        $response->assertSessionHas('flash_message', 'レビューを編集しました。');
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'score' => $validData['score'],
            'content' => $validData['content']
        ]);
    }

    public function test_admin_cannot_update_review()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => User::factory()->create()->id
        ]);

        $this->actingAs($admin, 'admin');

        $validData = [
            'score' => 4,
            'content' => '更新されたレビュー内容'
        ];

        $response = $this->put(route('restaurants.reviews.update', [
            'restaurant' => $restaurant,
            'review' => $review
        ]), $validData);
        $response->assertRedirect(route('admin.home'));
    }

    // レビュー削除機能のテスト
    public function test_guest_cannot_delete_review()
    {
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id
        ]);

        $response = $this->delete(route('restaurants.reviews.destroy', [
            'restaurant' => $restaurant,
            'review' => $review
        ]));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_delete_review()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('restaurants.reviews.destroy', [
            'restaurant' => $restaurant,
            'review' => $review
        ]));

        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_cannot_delete_others_review()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => User::factory()->create()->id
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('restaurants.reviews.destroy', [
            'restaurant' => $restaurant,
            'review' => $review
        ]));

        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
        $response->assertSessionHas('error_message', '不正なアクセスです。');
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_premium_user_can_delete_own_review()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('restaurants.reviews.destroy', [
            'restaurant' => $restaurant,
            'review' => $review
        ]));

        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
        $response->assertSessionHas('flash_message', 'レビューを削除しました。');
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_admin_cannot_delete_review()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->delete(route('restaurants.reviews.destroy', [
            'restaurant' => $restaurant,
            'review' => $review
        ]));

        $response->assertRedirect(route('admin.home'));
    }
}
