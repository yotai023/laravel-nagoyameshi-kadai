<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class FavoriteController extends Controller
{
    // indexアクション: お気に入り一覧ページ
    public function index()
    {
        $favorite_restaurants = auth()->user()
            ->favorite_restaurants()
            ->orderBy('restaurant_user.created_at', 'desc')
            ->paginate(15);

        return view('favorites.index', compact('favorite_restaurants'));
    }

    // storeアクション: お気に入り追加機能
    public function store(Restaurant $restaurant)
    {
        try {
            DB::beginTransaction();

            if (!auth()->user()->favorite_restaurants()->where('restaurant_id', $restaurant->id)->exists()) {
                auth()->user()->favorite_restaurants()->attach($restaurant->id);
                DB::commit();
                return redirect()->back()->with('flash_message', 'お気に入りに追加しました。');
            }

            DB::commit();
            return redirect()->back()->with('info_message', 'すでにお気に入りに追加されています。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error_message', 'お気に入りの追加に失敗しました。');
        }
    }

    // destroyアクション: お気に入り解除機能
    public function destroy(Restaurant $restaurant)
    {
        try {
            DB::beginTransaction();

            // 确保只删除当前用户和特定餐厅的关系
            $deleted = auth()->user()->favorite_restaurants()
                ->wherePivot('restaurant_id', $restaurant->id)
                ->detach();

            DB::commit();

            if ($deleted) {
                return redirect()->back()->with('flash_message', 'お気に入りを解除しました。');
            }

            return redirect()->back()->with('error_message', 'お気に入りが見つかりませんでした。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error_message', 'お気に入りの解除に失敗しました。');
        }
    }
}
