<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テストデータを作成
        $this->admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();
    }

    /**
     * Index action tests
     */
    public function test_guest_cannot_access_company_index()
    {
        $response = $this->get(route('admin.company.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_access_company_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.company.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_company_index()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.company.index'));
        $response->assertSuccessful();
        $response->assertViewIs('admin.company.index');
    }

    /**
     * Edit action tests
     */
    public function test_guest_cannot_access_company_edit()
    {
        $response = $this->get(route('admin.company.edit', $this->company));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_access_company_edit()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.company.edit', $this->company));
        $response->assertForbidden();
    }

    public function test_admin_can_access_company_edit()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.company.edit', $this->company));
        $response->assertSuccessful();
        $response->assertViewIs('admin.company.edit');
    }

    /**
     * Update action tests
     */
    public function test_guest_cannot_update_company()
    {
        $response = $this->put(route('admin.company.update', $this->company), [
            'name' => 'New Company Name',
            'postal_code' => '1234567',
            'address' => 'New Address',
            'representative' => 'New Representative',
            'establishment_date' => '2024-01-01',
            'capital' => '1000000',
            'business' => 'New Business',
            'number_of_employees' => '100'
        ]);
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_update_company()
    {
        $response = $this->actingAs($this->user)
            ->put(route('admin.company.update', $this->company), [
                'name' => 'New Company Name',
                'postal_code' => '1234567',
                'address' => 'New Address',
                'representative' => 'New Representative',
                'establishment_date' => '2024-01-01',
                'capital' => '1000000',
                'business' => 'New Business',
                'number_of_employees' => '100'
            ]);
        $response->assertForbidden();
    }

    public function test_admin_can_update_company()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.company.update', $this->company), [
                'name' => 'New Company Name',
                'postal_code' => '1234567',
                'address' => 'New Address',
                'representative' => 'New Representative',
                'establishment_date' => '2024-01-01',
                'capital' => '1000000',
                'business' => 'New Business',
                'number_of_employees' => '100'
            ]);
        
        $response->assertRedirect(route('admin.company.index'));
        $response->assertSessionHas('flash_message', '会社概要を編集しました。');
        
        $this->assertDatabaseHas('companies', [
            'id' => $this->company->id,
            'name' => 'New Company Name'
        ]);
    }
}
