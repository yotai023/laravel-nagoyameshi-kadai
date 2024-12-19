<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // 会員一覧ページのテスト

    public function test_guest_cannot_access_user_list()
    {
        $response = $this->get('/admin/users');
        $response->assertRedirect('/admin/login');
    }

    public function test_regular_user_cannot_access_user_list()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.index'));
        $response->assertRedirect('admin/login');
    }

    public function test_admin_can_access_user_list()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    // 会員詳細ページのテスト

    public function test_guest_cannot_access_user_detail()
    {
        $response = $this->get('/admin/users/1');
        $response->assertRedirect('/admin/login');
    }

    public function test_regular_user_cannot_access_user_detail()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.show', ['id' => 1]));
        $response->assertRedirect('admin/login');
    }

    public function test_admin_can_access_user_detail()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.users.show', ['id' => $user->id]));

        $response->assertStatus(200);
    }
}
