<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // 会員情報ページのテスト

    public function test_guest_cannot_access_user_page()
    {
        $response = $this->get(route('user.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_access_user_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('user.index'));

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_user_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('user.index'));

        $response->assertRedirect(route('admin.home'));
    }

    // 会員情報編集ページのテスト

    public function test_guest_cannot_access_user_edit_page()
    {
        $user = User::factory()->create();

        $response = $this->get(route('user.edit', ['user' => $user->id]));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_other_user_edit_page()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('user.edit', ['user' => $otherUser->id]));

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('error_message', '不正なアクセスです。');
    }

    public function test_regular_user_can_access_user_edit_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('user.edit', ['user' => $user->id]));

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_user_edit_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $user = User::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('user.edit', ['user' => $user->id]));

        $response->assertRedirect(route('admin.home'));
    }

    //会員情報更新機能のテスト

    public function test_guest_cannot_update_user_message()
    {
        $user = User::factory()->create();

        $response = $this->put(route('user.update', $user), [
            'name' => 'Updated Name',
            'kana' => 'カナ',
            'email' => 'updated@example.com',
            'postal_code' => '1234567',
            'address' => '東京都渋谷区',
            'phone_number' => '09012345678',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_update_other_user_message()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('user.update', $otherUser), [
                'name' => 'Updated Name',
                'kana' => 'カナ',
                'email' => 'updated@example.com',
                'postal_code' => '1234567',
                'address' => '東京都渋谷区',
                'phone_number' => '09012345678',
            ]);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('flash_message', '不正な操作です。');
    }

    public function test_regular_user_can_update_user_message()
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'kana' => 'カナ',
            'email' => 'updated@example.com',
            'postal_code' => '1234567',
            'address' => '東京都渋谷区',
            'phone_number' => '09012345678',
            'birthday' => '19900101',
            'occupation' => '会社員',
        ];

        $response = $this->actingAs($user)
            ->put(route('user.update', $user), $updateData);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('flash_message', '会員情報を編集しました。');
    }

    public function test_admin_cannot_update_user_messag()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $user = User::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->put(route('user.update', $user), [
                'name' => 'Updated Name',
                'kana' => 'カナ',
                'email' => 'updated@example.com',
                'postal_code' => '1234567',
                'address' => '東京都渋谷区',
                'phone_number' => '09012345678',
            ]);

        $response->assertRedirect(route('admin.home'));
    }
}
