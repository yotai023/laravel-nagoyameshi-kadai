<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    //カテゴリ一覧ページのテスト
    public function test_guest_cannot_access_admin_categories_index()
    {
        $response = $this->get(route('admin.categories.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_user_cannot_access_admin_categories_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.categories.index'));
        $response->assertRedirect('admin/login');
    }

    public function test_admin_can_access_admin_categories_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.categories.index'));
        $response->assertStatus(200);
    }

    //カテゴリ登録機能のテスト
    public function test_guest_cannot_store_category()
    {
        $response = $this->post(route('admin.categories.store'), [
            'name' => 'テストカテゴリ'
        ]);
        $response->assertRedirect('/admin/login');
    }

    public function test_user_cannot_store_category()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('admin.categories.store'), [
                'name' => 'テストカテゴリ'
            ]);
        $response->assertRedirect('admin/login');
    }

    public function test_admin_can_store_category()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.categories.store'), [
                'name' => 'テストカテゴリ'
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'テストカテゴリ']);
        $response->assertSessionHas('flash_message', 'カテゴリを登録しました。');
    }

    //カテゴリ更新機能のテスト
    public function test_guest_cannot_update_category()
    {
        $category = Category::factory()->create();

        $response = $this->put(route('admin.categories.update', $category), [
            'name' => '更新後のカテゴリ名'
        ]);
        $response->assertRedirect('/admin/login');
    }

    public function test_user_cannot_update_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('admin.categories.update', $category), [
                'name' => '更新後のカテゴリ名'
            ]);
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_update_category()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $category = Category::factory()->create();
        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.categories.update', $category), [
                'name' => '更新後のカテゴリ名'
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => '更新後のカテゴリ名']);
        $response->assertSessionHas('flash_message', 'カテゴリを編集しました。');
    }

    //カテゴリ削除機能のテスト
    public function test_guest_cannot_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->delete(route('admin.categories.destroy', $category));
        $response->assertRedirect('/admin/login');
    }

    public function test_user_cannot_delete_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('admin.categories.destroy', $category));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_delete_category()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $category = Category::factory()->create();
        $response = $this->actingAs($admin, 'admin')
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $response->assertSessionHas('flash_message', 'カテゴリを削除しました。');
    }
}
