<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
   /**
     * カテゴリ一覧を表示
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        $query = Category::query();

        // キーワードが存在する場合、部分一致検索を実行
        if (!empty($keyword)) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        // ページネーション適用（例：15件ずつ表示）
        $categories = $query->orderBy('id', 'asc')->paginate(15);

        // 総件数取得
        $total = $categories->total();

        return view('admin.categories.index', [
            'categories' => $categories,
            'keyword' => $keyword,
            'total' => $total
        ]);
    }

    /**
     * 新規カテゴリを登録
     */
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
        ]);

        // カテゴリ登録
        Category::create($request->all());

        $total = Category::count();

        return redirect()->route('admin.categories.index')->with('flash_message', 'カテゴリを登録しました。');
    }

    /**
     * カテゴリ情報を更新
     */
    public function update(Request $request, Category $category)
    {
        // バリデーション
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        // カテゴリ更新
        $category->update($request->all());
        return redirect()->route('admin.categories.index')->with('flash_message', 'カテゴリを編集しました。');
    }

    /**
     * カテゴリを削除
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('flash_message', 'カテゴリを削除しました。');
    }
}
