<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Category;

class HomeController extends Controller
{
    // Index アクション（トップページ）
    public function index()
    {
        // restaurantsテーブルから6つのデータを取得
        $highly_rated_restaurants = Restaurant::take(6)
            ->get();

        // categoriesテーブルのすべてのデータを取得
        $categories = Category::all();

        // 作成日時が新しい順にrestaurantsテーブルの6つのデータを取得
        $new_restaurants = Restaurant::orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // ビューにデータを渡す
        return view('home', [
            'highly_rated_restaurants' => $highly_rated_restaurants,
            'categories' => $categories,
            'new_restaurants' => $new_restaurants,
        ]);
    }
}
