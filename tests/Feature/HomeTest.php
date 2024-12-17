<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未ログインのユーザーは会員側のトップページにアクセスできる
     */
    public function test_guest_can_access_home_page()
    {
        $response = $this->get(route('home.index'));

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    /**
     * ログイン済みの一般ユーザーは会員側のトップページにアクセスできる
     */
    public function test_authenticated_user_can_access_home_page()
    {
        $user = User::factory()->create([
            'role' => 'user', // roleカラムが一般ユーザー
        ]);

        $response = $this->actingAs($user)->get(route('home.index'));

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    /**
     * ログイン済みの管理者は会員側のトップページにアクセスできない
     */
    public function test_admin_cannot_access_home_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin', // roleカラムが管理者
        ]);

        $response = $this->actingAs($admin)->get(route('home.index'));

        $response->assertRedirect(route('admin.dashboard')); // リダイレクト先が管理者ページを想定
    }
}
