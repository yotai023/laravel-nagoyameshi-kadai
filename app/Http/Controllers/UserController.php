<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * 会員情報ページを表示
     */
    public function index()
    {
        $user = Auth::user();

        return view('user.index', ['user' => $user]);
    }

    /**
     * 会員情報編集ページを表示
     */
    public function edit(User $user)
    {
        if ($user->id !== Auth::id()) {
            session()->flash('error_message', '不正なアクセスです。');

            return redirect()->route('user.index');
        }

        return view('user.edit', ['user' => $user]);
    }

    /**
     * 会員情報更新機能を表示
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->id !== $user->id) {
            return redirect()
                ->route('user.index')
                ->with('flash_message', '不正な操作です。');
        }

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
            'phone_number' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (substr_count($value, '-') !== 2) {
                        $fail('電話番号はハイフン2つを含める必要があります。');
                        return;
                    }

                    $digitsOnly = preg_replace('/[^0-9]/', '', $value);

                    if (strlen($digitsOnly) < 10 || strlen($digitsOnly) > 11) {
                        $fail('電話番号は10桁から11桁の数字で入力してください。');
                        return;
                    }

                    if (!preg_match('/^(\d{2,4})-(\d{2,4})-(\d{3,4})$/', $value)) {
                        $fail('電話番号の形式が正しくありません。');
                        return;
                    }

                    $parts = explode('-', $value);
                    $areaCode = $parts[0];
                    if (strlen($areaCode) < 2 || strlen($areaCode) > 4) {
                        $fail('電話番号の形式が正しくありません。');
                    }
                }
            ],
            'birthday' => 'nullable|digits:8',
            'occupation' => 'nullable|string|max:255',
        ]);

        $user->update($validatedData);

        return redirect()
            ->route('user.index')
            ->with('flash_message', '会員情報を編集しました。');
    }
}
