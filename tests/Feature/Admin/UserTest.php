<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;

class UserTest extends TestCase
{
    use RefreshDatabase;

    //管理者側の会員一覧ページのテスト
    public function test_guest_cannot_access_admin_user_page()
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_regular_user_cannot_access_admin_user_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_admin_uder_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    //管理者側の会員詳細ページのテスト
    public function test_guest_cannot_access_admin_user_detail_page()
    {
        $user = User::factory()->create();

        $response = $this->get(route('admin.users.show',$user));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_regular_user_cannot_access_admin_user_detail_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.show',$user));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_admin_uder_detail_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();
        
        $user = User::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.users.show',$user));
        $response->assertStatus(200);
    }
}
