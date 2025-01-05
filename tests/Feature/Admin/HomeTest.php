<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_home()
    {
        $response = $this->get(route('admin.home'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_access_admin_home()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.home'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_term_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.home'));

        $response->assertStatus(200);
    }
}
