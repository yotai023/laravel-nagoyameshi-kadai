<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * 会社概要ページを表示
     */
    public function index()
    {
        $company = Company::first();
        return view('admin.company.index', compact('company'));
    }

    /**
     * 会社概要編集ページを表示
     */
    public function edit(Company $company)
    {
        return view('admin.company.edit', compact('company'));
    }

    /**
     * 会社概要を更新
     */
    public function update(Request $request, Company $company)
    {
        // バリデーション定義
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'postal_code' => 'required|numeric|digits:7',
            'address' => 'required',
            'representative' => 'required',
            'establishment_date' => 'required',
            'capital' => 'required',
            'business' => 'required',
            'number_of_employees' => 'required'
        ]);

        // バリデーション失敗時の処理
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        // データ更新
        $company->update($request->all());

        // リダイレクト
        return redirect()
            ->route('admin.company.index')
            ->with('flash_message', '会社概要を編集しました。');
    }
}
