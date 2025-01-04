<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Term;

class TermController extends Controller
{
    /**
     * 利用規約ページを表示
     */
    public function index()
    {
        $term = Term::first();
        return view('admin.terms.index', compact('term'));
    }

    /**
     * 利用規約編集ページを表示
     */
    public function edit(Term $term)
    {
        return view('admin.terms.edit', compact('term'));
    }

    /**
     * 利用規約を更新
     */
    public function update(Request $request, Term $term)
    {
        // バリデーション
        $request->validate([
            'content' => 'required'
        ]);

        // データ更新
        $term->update($request->all());

        // リダイレクト
        return redirect()
            ->route('admin.terms.index')
            ->with('flash_message', '利用規約を編集しました。');
    }
}
