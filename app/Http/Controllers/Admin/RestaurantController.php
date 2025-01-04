<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
//use App\Models\Category;
//use App\Models\RegularHoliday;

class RestaurantController extends Controller
{
    /**
     * 店舗一覧ページを表示
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $query = Restaurant::query();

        if (!empty($keyword)) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $restaurants = $query->paginate(10);
        $total = $restaurants->total();

        return view('admin.restaurants.index', compact('restaurants', 'keyword', 'total'));
    }


    /**
     * 店舗詳細を表示
     */
    public function show(Restaurant $restaurant)
    {
        return view('admin.restaurants.show', compact('restaurant'));
    }


    /**
     * 店舗登録ページを表示
     */
    public function create()
    {
        // $categories = Category::all();
        // $regular_holidays = RegularHoliday::all();

        return view('admin.restaurants.create', [
            //   'regular_holidays' => $regular_holidays,
            //   'categories' => $categories,
        ]);
    }

    /**
     * 店舗を新規登録
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,bmp,gif,svg,webp|max:2048',
            'description' => 'required|string|max:255',
            'lowest_price' => 'required|numeric|min:0|lte:highest_price',
            'highest_price' => 'required|numeric|min:0|gte:lowest_price',
            'postal_code' => 'required|numeric|digits:7',
            'address' => 'required|string|max:255',
            'opening_time' => 'required|date_format:H:i|before:closing_time',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'seating_capacity' => 'nullable|numeric|min:0',
        ]);

        $restaurant = new Restaurant($request->except('image'));
       

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $imageData = base64_encode(file_get_contents($image->path()));
            $restaurant->image_data = $imageData;
        }

        /* $category_ids = array_filter($request->input('category_ids'));
        $restaurant->categories()->sync($category_ids);

        if ($request->has('regular_holiday_ids')) {
            $restaurant->regularHolidays()->sync($request->regular_holiday_ids);
        }*/

        $restaurant->save();

        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を登録しました。');
    }

    /**
     * 店舗編集フォームを表示
     */
    public function edit(Restaurant $restaurant)
    {
        // $categories = Category::all();
        // $category_ids = $restaurant->categories->pluck('id')->toArray();

        // $regular_holidays = RegularHoliday::all();

        return view('admin.restaurants.edit', [
            'restaurant' => $restaurant,
            //'categories' => $categories, 
            // 'category_ids' => $category_ids,
            // 'regular_holidays' => $regular_holidays,
        ]);
    }

    /**
     * 店舗情報を更新
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,bmp,gif,svg,webp|max:2048',
            'description' => 'required|string|max:255',
            'lowest_price' => 'required|numeric|min:0|lte:highest_price',
            'highest_price' => 'required|numeric|min:0|gte:lowest_price',
            'postal_code' => 'required|numeric|digits:7',
            'address' => 'required|string|max:255',
            'opening_time' => 'required|date_format:H:i|before:closing_time',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'seating_capacity' => 'nullable|numeric|min:0',
        ]);

        $restaurant->fill($request->except('image'));

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageData = base64_encode(file_get_contents($image->path()));
            $restaurant->image_data = $imageData;
        }

        $restaurant->save();

        /* $category_ids = array_filter($request->input('category_ids'));
       $restaurant->categories()->sync($category_ids);

       $restaurant->regular_holidays()->sync($request->regular_holiday_ids ?? []);*/

        return redirect()->route('admin.restaurants.index')
            ->withInput(['page' => $request->page])
            ->with('flash_message', '店舗情報を編集しました。');
    }

    /**
     * 店舗を削除
     */
    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を削除しました。');
    }
}
