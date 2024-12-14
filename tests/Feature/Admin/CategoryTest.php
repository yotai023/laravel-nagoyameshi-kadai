<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザーを作成
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // 一般ユーザーを作成
        $this->user = User::factory()->create(['role' => 'user']);
    }

    /**
     * index アクションのテスト
     */
    public function test_guest_cannot_access_admin_categories_index()
    {
        $response = $this->get(route('admin.categories.index'));
        $response->assertRedirect('/login');
    }

    public function test_user_cannot_access_admin_categories_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.categories.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_categories_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.index'));
        $response->assertOk();
    }

    /**
     * store アクションのテスト
     */
    public function test_guest_cannot_store_category()
    {
        $response = $this->post(route('admin.categories.store'), [
            'name' => 'テストカテゴリ'
        ]);
        $response->assertRedirect('/login');
    }

    public function test_user_cannot_store_category()
    {
        $response = $this->actingAs($this->user)
            ->post(route('admin.categories.store'), [
                'name' => 'テストカテゴリ'
            ]);
        $response->assertForbidden();
    }

    public function test_admin_can_store_category()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'テストカテゴリ'
            ]);
        
        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'テストカテゴリ']);
        $response->assertSessionHas('flash_message', 'カテゴリを登録しました。');
    }

    /**
     * update アクションのテスト
     */
    public function test_guest_cannot_update_category()
    {
        $category = Category::factory()->create();
        $response = $this->put(route('admin.categories.update', $category), [
            'name' => '更新後のカテゴリ名'
        ]);
        $response->assertRedirect('/login');
    }

    public function test_user_cannot_update_category()
    {
        $category = Category::factory()->create();
        $response = $this->actingAs($this->user)
            ->put(route('admin.categories.update', $category), [
                'name' => '更新後のカテゴリ名'
            ]);
        $response->assertForbidden();
    }

    public function test_admin_can_update_category()
    {
        $category = Category::factory()->create();
        $response = $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), [
                'name' => '更新後のカテゴリ名'
            ]);
        
        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => '更新後のカテゴリ名']);
        $response->assertSessionHas('flash_message', 'カテゴリを編集しました。');
    }

    /**
     * destroy アクションのテスト
     */
    public function test_guest_cannot_delete_category()
    {
        $category = Category::factory()->create();
        $response = $this->delete(route('admin.categories.destroy', $category));
        $response->assertRedirect('/login');
    }

    public function test_user_cannot_delete_category()
    {
        $category = Category::factory()->create();
        $response = $this->actingAs($this->user)
            ->delete(route('admin.categories.destroy', $category));
        $response->assertForbidden();
    }

    public function test_admin_can_delete_category()
    {
        $category = Category::factory()->create();
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));
        
        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $response->assertSessionHas('flash_message', 'カテゴリを削除しました。');
    }
}
