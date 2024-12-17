<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('user.index', ['user' => $user]);
    }

    public function edit(User $user)
    {
        if ($user->id !== Auth::id()) {
            session()->flash('error_message', '不正なアクセスです。');

            return redirect()->route('user.index');
        }

        return view('user.edit', ['user' => $user]);
    }

    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();

        if ($currentUser->id != $id) {
            return redirect()
                ->route('profile.edit')
                ->with('flash_message', '不正な操作です。');
        }

        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'kana' => 'required|string|regex:/^[ァ-ヶー]+$/u|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'postal_code' => 'required|digits:7',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|digits_between:10,11',
            'birthday' => 'nullable|digits:8',
            'occupation' => 'nullable|string|max:255',
        ]);

        $user->update($validatedData);

        return redirect()
            ->route('profile.edit')
            ->with('flash_message', '会員情報を編集しました。');
    }
}
