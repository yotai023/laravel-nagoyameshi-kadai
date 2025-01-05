<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Reservation;

class HomeController extends Controller
{
    public function index()
    {
        // 全ユーザー数を取得
        $total_users = User::count();

        // 有料会員数を取得 (stripe_status が active のものをカウント)
        $total_premium_users = DB::table('subscriptions')
            ->where('stripe_status', 'active')
            ->count();

        // 無料会員数を計算 (全体から有料会員を引く)
        $total_free_users = $total_users - $total_premium_users;

        // レストラン総数を取得
        $total_restaurants = Restaurant::count();

        // 予約総数を取得
        $total_reservations = Reservation::count();

        // 月間売上を計算 (月額300円 × 有料会員数)
        $sales_for_this_month = 300 * $total_premium_users;

        // ビューに渡すデータを配列にまとめる
        $data = [
            'total_users' => $total_users,
            'total_premium_users' => $total_premium_users,
            'total_free_users' => $total_free_users,
            'total_restaurants' => $total_restaurants,
            'total_reservations' => $total_reservations,
            'sales_for_this_month' => $sales_for_this_month,
        ];

        // admin/home ビューを表示し、データを渡す
        return view('admin.home', $data);
    }
}
