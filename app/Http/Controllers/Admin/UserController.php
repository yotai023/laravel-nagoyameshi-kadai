<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword'); 
        $query = User::query(); 

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('kana', 'like', "%{$keyword}%");
            });
        }

        $users = $query->paginate(10);
        $total = $users->total(); 

        return view('admin.users.index', compact('users', 'keyword', 'total'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.show',compact('user'));
    }
}
