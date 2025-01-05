<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /** 
     * レビュー一覧ページ
     */
    public function index($restaurant_id)
    {
        $restaurant = Restaurant::findOrFail($restaurant_id);
        $user = Auth::user();

        if ($user->is_premium) {
            $reviews = Review::with('user')
                ->where('restaurant_id', $restaurant_id)
                ->orderBy('created_at', 'desc')
                ->paginate(5);
        } else {
            $reviews = Review::with('user')
                ->where('restaurant_id', $restaurant_id)
                ->orderBy('created_at', 'desc')
                ->paginate(3);
        }

        return view('reviews.index', compact('restaurant', 'reviews'));
    }

    /** 
     * レビュー投稿ページ
     */
    public function create(Restaurant $restaurant)
    {
        $user = Auth::user();

        if (!$user->subscribed('premium_plan')) {
            return redirect()->route('subscription.create');
        }

        return view('reviews.create', ['restaurant' => $restaurant]);
    }

    /**
     * レビュー投稿機能のテスト
     */
    public function store(Request $request, Restaurant $restaurant)
    {
        $user = Auth::user();

        if (!$user->subscribed('premium_plan')) {
            return redirect()->route('subscription.create');
        }

        $request->validate([
            'score' => 'required|integer|min:1|max:5',
            'content' => 'required',
        ]);

        $review = new Review();
        $review->score = $request->score;
        $review->content = $request->content;
        $review->restaurant_id = $restaurant->id;
        $review->user_id = Auth::id();
        $review->save();

        return redirect()->route('restaurants.reviews.index', $restaurant)
            ->with('flash_message', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集ページのテスト
     */
    public function edit(Restaurant $restaurant, Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            return redirect()->route('restaurants.reviews.index', $restaurant)
                ->with('error_message', '不正なアクセスです。');
        }

        return view('reviews.edit', [
            'restaurant' => $restaurant,
            'review' => $review,
        ]);
    }

    /**
     * レビュー更新機能
     */
    public function update(Request $request, Restaurant $restaurant, Review $review)
    {
        if (!Auth::user()->subscribed('premium_plan')) {
            return redirect()->route('subscription.create');
        }

        if ($review->user_id !== Auth::id()) {
            return redirect()->route('restaurants.reviews.index', $restaurant)
                ->with('error_message', '不正なアクセスです。');
        }

        $request->validate([
            'score' => 'required|integer|min:1|max:5',
            'content' => 'required',
        ]);

        $review->update([
            'score' => $request->score,
            'content' => $request->content,
        ]);

        return redirect()->route('restaurants.reviews.index', $restaurant)
            ->with('flash_message', 'レビューを編集しました。');
    }

    /**
     * レビュー削除機能
     */
    public function destroy(Restaurant $restaurant, Review $review)
    {
        if (!Auth::user()->subscribed('premium_plan')) {
            return redirect()->route('subscription.create');
        }

        if ($review->user_id !== Auth::id()) {
            return redirect()->route('restaurants.reviews.index', $restaurant)
                ->with('error_message', '不正なアクセスです。');
        }

        $review->delete();

        return redirect()->route('restaurants.reviews.index', $restaurant)
            ->with('flash_message', 'レビューを削除しました。');
    }
}
