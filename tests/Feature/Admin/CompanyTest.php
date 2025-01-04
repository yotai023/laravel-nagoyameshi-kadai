<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    //会社概要ページのテスト

    public function test_guest_cannot_access_company_index()
    {
        $response = $this->get(route('admin.company.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_access_company_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.company.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_company_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $company = Company::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.company.index'));

        $response->assertStatus(200);
    }

    //会社概要編集ページのテスト
   public function test_guest_cannot_access_company_edit()
    {
        $company = Company::factory()->create();

        $response = $this->get(route('admin.company.edit', $company));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_cannot_access_company_edit()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.company.edit', ['company' => $company->id]));
            
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_company_edit()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $company = Company::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.company.edit', ['company' => $company->id]));

        $response->assertStatus(200);
    }

    //会社概要更新機能のテスト
    public function test_guest_cannot_update_company()
    {
        $company = Company::factory()->create();

        $response = $this->put(route('admin.company.update', $company), [
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
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('admin.company.update', $company), [
                'name' => 'New Company Name',
                'postal_code' => '1234567',
                'address' => 'New Address',
                'representative' => 'New Representative',
                'establishment_date' => '2024-01-01',
                'capital' => '1000000',
                'business' => 'New Business',
                'number_of_employees' => '100'
            ]);
            $response->assertRedirect(route('admin.login'));;
    }

    public function test_admin_can_update_company()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $company = Company::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.company.update', $company), [
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
            'id' => $company->id,
            'name' => 'New Company Name'
        ]);
    }
}
