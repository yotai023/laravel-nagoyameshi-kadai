<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Company;

class CompanyTest extends TestCase 
{
    use RefreshDatabase;
    
    /**
     * 未ログインのユーザーは会員側の会社概要ページにアクセスできる
     */
    public function test_guest_can_access_company_page()
    {
        Company::factory()->create();
        
        $response = $this->get(route('company.index'));

        $response->assertStatus(200);
    }

    /**
     * ログイン済みの一般ユーザーは会員側の会社概要ページにアクセスできる
     */
    public function test_authenticated_user_can_access_company_page()
    {
        Company::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('company.index'));
        $response->assertStatus(200);
    }

    /**
     * ログイン済みの管理者は会員側の会社概要ページにアクセスできない
     */
    public function test_admin_cannot_access_company_page()
    {
        Company::factory()->create();
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('company.index'));

        $response->assertStatus(302);
        $response->assertRedirect('/admin/home'); 
    }
}