<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Term;
use Illuminate\Support\Facades\Hash;

class TermTest extends TestCase
{
    use RefreshDatabase;

    //利用規約ページのテスト
    public function test_guest_cannot_access_term_index()
    {
        $response = $this->get(route('admin.terms.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_access_term_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.terms.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_term_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $term = Term::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.terms.index'));

        $response->assertStatus(200);
    }

    //利用規約編集ページのテスト

    public function test_guest_cannot_access_term_edit()
    {
        $term = Term::factory()->create();

        $response = $this->get(route('admin.terms.edit', $term));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_access_term_edit()
    {
        $user = User::factory()->create();
        $term = Term::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.terms.edit', $term));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_term_edit()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $term = Term::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.terms.edit', $term));

        $response->assertStatus(200);
    }

    //利用規約更新機能のテスト
    public function test_guest_cannot_update_term()
    {
        $term = Term::factory()->create();

        $response = $this->put(route('admin.terms.update', $term), [
            'content' => 'New Terms Content'
        ]);
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_update_term()
    {
        $user = User::factory()->create();
        $term = Term::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('admin.terms.update', $term), [
                'content' => 'New Terms Content'
            ]);
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_update_term()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $term = Term::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.terms.update', $term), [
                'content' => 'New Terms Content'
            ]);

        $response->assertRedirect(route('admin.terms.index'));
        $response->assertSessionHas('flash_message', '利用規約を編集しました。');

        $this->assertDatabaseHas('terms', [
            'id' => $term->id,
            'content' => 'New Terms Content'
        ]);
    }
}
