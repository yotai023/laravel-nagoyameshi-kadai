<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index()
    {
        $company = Company::firstOrNew([], [
            'name' => '会社名未設定',
            'postal_code' => '',
            'address' => '',
            'representative' => '',
            'establishment_date' => '',
            'capital' => '',
            'business' => '',
            'number_of_employees' => ''
        ]);
        
        return view('company.index', compact('company'));
    }
}