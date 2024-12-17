<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TermControllerTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $term;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テストデータを作成
        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();
        $this->term = Term::factory()->create();
    }

    /**
     * Index action tests
     */
    public function test_guest_cannot_access_term_index()
    {
        $response = $this->get(route('admin.terms.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_access_term_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.terms.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_term_index()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.terms.index'));
        $response->assertSuccessful();
        $response->assertViewIs('admin.terms.index');
    }

    /**
     * Edit action tests
     */
    public function test_guest_cannot_access_term_edit()
    {
        $response = $this->get(route('admin.terms.edit', $this->term));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_access_term_edit()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.terms.edit', $this->term));
        $response->assertForbidden();
    }

    public function test_admin_can_access_term_edit()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.terms.edit', $this->term));
        $response->assertSuccessful();
        $response->assertViewIs('admin.terms.edit');
    }

    /**
     * Update action tests
     */
    public function test_guest_cannot_update_term()
    {
        $response = $this->put(route('admin.terms.update', $this->term), [
            'content' => 'New Terms Content'
        ]);
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_update_term()
    {
        $response = $this->actingAs($this->user)
            ->put(route('admin.terms.update', $this->term), [
                'content' => 'New Terms Content'
            ]);
        $response->assertForbidden();
    }

    public function test_admin_can_update_term()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.terms.update', $this->term), [
                'content' => 'New Terms Content'
            ]);
        
        $response->assertRedirect(route('admin.terms.index'));
        $response->assertSessionHas('flash_message', '利用規約を編集しました。');
        
        $this->assertDatabaseHas('terms', [
            'id' => $this->term->id,
            'content' => 'New Terms Content'
        ]);
    }
}