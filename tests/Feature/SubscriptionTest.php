<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Mockery;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    // 有料プラン登録ページのテスト
    public function test_guest_cannot_access_subscription_create_page()
    {
        $response = $this->get(route('subscription.create'));
        $response->assertRedirect('/login');
    }

    public function test_regular_user_can_access_subscription_create_page()
    {
        $user = User::factory()->create([
            'stripe_id' => null
        ]);

        $response = $this->actingAs($user)->get(route('subscription.create'));
        $response->assertStatus(200);
    }

    public function test_premium_user_cannot_access_subscription_create_page()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);

        $response = $this->actingAs($user)->get(route('subscription.create'));
        $response->assertRedirect('/user');
    }

    public function test_admin_cannot_access_subscription_create_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('subscription.create'));
        $response->assertRedirect('/admin/home');
    }

    // 有料プラン登録機能のテスト
    public function test_guest_cannot_add_subscription()
    {
        $response = $this->post(route('subscription.store'), [
            'paymentMethodId' => 'pm_card_visa'
        ]);
        $response->assertRedirect('/login');
    }

    public function test_regular_user_can_add_subscription()
    {
        $baseUser = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);
    
        // Mock SubscriptionBuilder
        $subscriptionBuilder = Mockery::mock('Laravel\Cashier\SubscriptionBuilder');
        $subscriptionBuilder->shouldReceive('create')
            ->once()
            ->with('pm_card_visa')
            ->andReturn(true);
    
        // Mock User
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill($baseUser->getAttributes());
    
        $user->shouldReceive('subscribed')
            ->with('premium_plan')
            ->andReturn(false);
    
        $user->shouldReceive('newSubscription')
            ->once()
            ->with('premium_plan', config('services.stripe.price_id'))
            ->andReturn($subscriptionBuilder);
    
        // 使用 actingAs 模拟登录
        $this->actingAs($user);
    
        // 发送 POST 请求
        $response = $this->post(route('subscription.store'), [
            'paymentMethodId' => 'pm_card_visa'
        ]);
    
        // 验证 SubscriptionBuilder 的 create 方法被调用
        $subscriptionBuilder->shouldHaveReceived('create')
            ->with('pm_card_visa');
    
        // 验证重定向
        $response->assertRedirect(route('user.index'));
    }

    public function test_premium_user_cannot_add_subscription()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);

        $premiumUser = Mockery::mock(User::class)->makePartial();
        $premiumUser->shouldReceive('subscribed')
            ->with('premium_plan')
            ->andReturn(true);

        foreach ($user->getAttributes() as $key => $value) {
            $premiumUser->{$key} = $value;
        }

        $response = $this->actingAs($premiumUser)
            ->from(route('subscription.create'))
            ->post(route('subscription.store'), [
                'paymentMethodId' => 'pm_card_visa'
            ]);

        $response->assertRedirect(route('user.index'));
    }

    public function test_admin_cannot_add_subscription()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->post(route('subscription.store'), [
            'plan' => 'premium'
        ]);
        $response->assertRedirect('/admin/home');
    }

    // お支払い方法編集ページのテスト
    public function test_guest_cannot_access_subscription_edit_page()
    {
        $response = $this->get(route('subscription.edit'));
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_subscription_edit_page()
    {
        $user = new class(User::factory()->create([
            'stripe_id' => null
        ])->toArray()) extends User {
            public function subscribed($plan)
            {
                return false;
            }
        };

        $response = $this->actingAs($user)
            ->get(route('subscription.edit'));

        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_can_access_subscription_edit_page()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);

        $premiumUser = new class($user->toArray()) extends User {
            public $exists = true;

            public function __construct(array $attributes)
            {
                parent::fill($attributes);
                $this->id = $attributes['id'] ?? 1;
            }

            public function subscribed($plan)
            {
                return true;
            }

            public function createSetupIntent($options = [])
            {
                return (object)[
                    'client_secret' => 'test_secret'
                ];
            }

            public function defaultPaymentMethod()
            {
                return (object)[
                    'card' => (object)[
                        'brand' => 'visa',
                        'last4' => '4242'
                    ],
                    'billing_details' => (object)[
                        'name' => 'Test User'
                    ]
                ];
            }

            public function getPmTypeAttribute()
            {
                return 'visa';
            }

            public function getPmLastFourAttribute()
            {
                return '4242';
            }
        };

        $response = $this->actingAs($premiumUser)->get(route('subscription.edit'));
        $response->assertStatus(200);
        $response->assertViewIs('subscription.edit');
        $response->assertViewHas(['user', 'intent']);
    }

    public function test_admin_cannot_access_subscription_edit_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('subscription.edit'));
        $response->assertRedirect('/admin/home');
    }

    // お支払い方法更新機能のテスト
    public function test_guest_cannot_update_payment_method()
    {
        $response = $this->post(route('subscription.update'), [
            'paymentMethodId' => 'pm_card_mastercard'
        ]);

        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_update_payment_method()
    {
        $user = User::factory()->create();

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('subscribed')
            ->with('premium_plan')
            ->andReturn(false);

        $response = $this->actingAs($user)
            ->from(route('subscription.edit'))
            ->patch(route('subscription.update'), [
                'paymentMethodId' => 'pm_card_mastercard'
            ]);

        $response->assertRedirect(route('subscription.create'));
    }


    public function test_premium_user_can_update_payment_method()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);

        $premiumUser = Mockery::mock(User::class)->makePartial();
        $premiumUser->shouldReceive('subscribed')
            ->with('premium_plan')
            ->andReturn(true);
        $premiumUser->shouldReceive('updateDefaultPaymentMethod')
            ->andReturn(true);
        $premiumUser->shouldReceive('defaultPaymentMethod')
            ->andReturn((object)[
                'card' => (object)[
                    'brand' => 'visa',
                    'last4' => '4242'
                ]
            ]);

        foreach ($user->getAttributes() as $key => $value) {
            $premiumUser->{$key} = $value;
        }

        $response = $this->actingAs($premiumUser)
            ->from(route('subscription.edit'))
            ->patch(route('subscription.update'), [
                'paymentMethodId' => 'pm_card_mastercard'
            ]);

        $response->assertRedirect(route('user.index'));
    }

    public function test_admin_cannot_update_payment_method()
    {
        $admin = Admin::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password')
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('subscription.update'), [
                'paymentMethodId' => 'pm_card_mastercard'
            ]);

        $response->assertRedirect('/admin/home');
    }

    // 有料プラン解約ページのテスト
    public function test_guest_cannot_access_subscription_cancel_page()
    {
        $response = $this->get(route('subscription.cancel'));
        $response->assertRedirect('/login');
    }

    public function test_regular_user_can_access_subscription_cancel_page()
    {
        $user = User::factory()->create([
            'stripe_id' => null
        ]);

        $response = $this->actingAs($user)->get(route('subscription.cancel'));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_cannot_access_subscription_cancel_page()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);

        $response = $this->actingAs($user)->get(route('subscription.cancel'));
        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_subscription_cancel_page()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('subscription.cancel'));
        $response->assertRedirect('/admin/home');
    }

    // 有料プラン解約機能のテスト
    public function test_guest_cannot_cancel_subscription()
    {
        $response = $this->post(route('subscription.destroy'));
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_cancel_subscription()
    {
        $user = User::factory()->create();

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('subscribed')
            ->with('premium_plan')
            ->andReturn(false);

        $response = $this->actingAs($user)
            ->from(route('subscription.cancel'))
            ->delete(route('subscription.destroy'));

        $response->assertRedirect(route('subscription.create'));
    }

    public function test_premium_user_can_cancel_subscription()
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123456789'
        ]);

        $subscription = Mockery::mock(\Laravel\Cashier\Subscription::class);
        $subscription->shouldReceive('cancelNow')->once()->andReturn(true);

        $premiumUser = Mockery::mock(User::class)->makePartial();
        $premiumUser->shouldReceive('subscribed')
            ->with('premium_plan')
            ->andReturn(true);
        $premiumUser->shouldReceive('subscription')
            ->with('premium_plan')
            ->andReturn($subscription);

        foreach ($user->getAttributes() as $key => $value) {
            $premiumUser->{$key} = $value;
        }

        $response = $this->actingAs($premiumUser)
            ->delete(route('subscription.destroy'));

        $response->assertRedirect(route('user.index'));
    }

    public function test_admin_cannot_cancel_subscription()
    {
        $admin = Admin::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password')
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('subscription.destroy'));
        $response->assertRedirect('/admin/home');
    }
}
