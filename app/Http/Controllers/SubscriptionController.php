<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\CardException;
use Exception;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('not.subscribed')->only(['create', 'store']);
        $this->middleware('subscribed')->except(['create', 'store']);
    }

    /**
     * 有料プラン登録ページ
     */
    public function create()
    {
        $user = Auth::user();

        // すでにStripe顧客IDを持っているユーザーをリダイレクト
        if ($user->stripe_id && $user->subscribed('premium_plan')) {
            return redirect()->route('user.index')
                ->with('info', 'すでに有料プランに登録されています。');
        }

        try {
            $intent = $user->createSetupIntent();

            return view('subscription.create', [
                'intent' => $intent,
                'user' => $user
            ]);
        } catch (Exception $e) {
            Log::error('Setup intent creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', '決済の準備に失敗しました。しばらく時間をおいて再度お試しください。');
        }
    }

    /**
     * 有料プラン登録機能
     */
    public function store(Request $request)
    {
        $request->validate([
            'paymentMethodId' => 'required|string'
        ]);

        $user = Auth::user();

        try {
            $user->newSubscription('premium_plan', config('services.stripe.price_id'))
                ->create($request->paymentMethodId);

            return redirect()->route('user.index')
                ->with('flash_message', '有料プランへの登録が完了しました。');
        } catch (CardException $e) {
            Log::error('Stripe card error', ['error' => $e->getMessage()]);
            return redirect()->route('user.index')
                ->with('error', 'カード情報の検証に失敗しました：' . $e->getMessage());
        } catch (IncompletePayment $e) {
            Log::error('Incomplete payment error', ['error' => $e->getMessage()]);
            return redirect()->route('cashier.payment', [$e->payment->id])
                ->with('error', '支払いの確認が必要です。');
        } catch (Exception $e) {
            Log::error('Subscription creation failed', ['error' => $e->getMessage()]);
            return redirect()->route('user.index')
                ->with('error', 'サブスクリプションの作成に失敗しました。');
        }
    }

    /**
     * お支払い方法編集ページ
     */
    public function edit()
    {
        $user = Auth::user();

        if (!$user->subscribed('premium_plan')) {
            return redirect()->route('subscription.create')
                ->with('info', '有料プランに未登録です。');
        }

        try {
            $intent = $user->createSetupIntent();
            $paymentMethod = $user->defaultPaymentMethod();

            return view('subscription.edit', [
                'user' => $user,
                'intent' => $intent,
                'paymentMethod' => $paymentMethod
            ]);
        } catch (Exception $e) {
            Log::error('Payment method edit page error', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', '支払い方法の取得に失敗しました。');
        }
    }

    /**
     * お支払い方法更新機能
     */
    public function update(Request $request)
    {
        $request->validate([
            'paymentMethodId' => 'required|string'
        ]);

        $user = Auth::user();

        try {
            $user->updateDefaultPaymentMethod($request->paymentMethodId);

            // 支払い方法のメタデータを更新
            $paymentMethod = $user->defaultPaymentMethod();
            $user->update([
                'pm_type' => $paymentMethod->card->brand,
                'pm_last_four' => $paymentMethod->card->last4,
            ]);

            return redirect()->route('user.index')
                ->with('flash_message', 'お支払い方法を変更しました。');
        } catch (Exception $e) {
            return redirect()->route('user.index')
                ->with('error', '支払い方法の更新に失敗しました。');
        }
    }

    /**
     * 有料プラン解約ページ
     */
    public function cancel()
    {
        $user = Auth::user();

        if (!$user->subscribed('premium_plan')) {
            return redirect()->route('subscription.create')
                ->with('info', '有料プランに未登録です。');
        }

        return view('subscription.cancel', [
            'user' => $user,
        ]);
    }

    /**
     * 有料プラン解約機能
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

    try {
        Log::info('Attempting to cancel subscription', [
            'user_id' => $user->id,
            'request_method' => $request->method()
        ]);

        if (!$user->subscription('premium_plan')) {
            return redirect()->route('user.index')
                ->with('error', '有料プランに未登録です。');
        }

        // 即時解約を実行
        $user->subscription('premium_plan')->cancelNow();

        // 支払い方法の情報をクリア
        $user->update([
            'pm_type' => null,
            'pm_last_four' => null,
            'trial_ends_at' => null,
        ]);

        Log::info('Subscription cancelled successfully', ['user_id' => $user->id]);

        return redirect()->route('user.index')
            ->with('flash_message', '有料プランを解約しました。');
            
    } catch (Exception $e) {
        Log::error('Subscription cancellation failed', [
            'error' => $e->getMessage(),
            'user_id' => $user->id
        ]);
        
        return redirect()->back()
            ->with('error', '解約処理に失敗しました。');
    }
    }
}
