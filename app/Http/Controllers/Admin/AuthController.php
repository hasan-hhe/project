<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function getLogin()
    {
        if (Auth::user()) {
            return redirect()->route('admin.dashboard.index');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $phoneNumber = $request->phone_number;
        $password = $request->password;
        $remember = $request->has('remember');

        // البحث عن المستخدم باستخدام phone_number
        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'هذا الحساب غير موجود!');
        }

        // التحقق من أن الحساب من نوع ADMIN فقط
        if ($user->account_type != 'ADMIN') {
            return redirect()->back()->with('error', 'هذا الحساب لا يملك صلاحية للدخول!');
        }

        // التحقق من كلمة المرور
        if (!Hash::check($password, $user->password)) {
            return redirect()->back()->with('error', 'كلمة المرور غير صحيحة!');
        }

        // تسجيل الدخول
        Auth::login($user, $remember);

        return redirect()->route('admin.dashboard.index');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.auth.login')->with('success', 'تم تسجيل الخروج بنجاح!');
    }
}
