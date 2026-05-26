<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {

            return back()->withErrors(['email' => 'Email atau kata sandi salah'])->onlyInput('email');
        }

        $user = Auth::user();

        $hasShopeeToken = auth()->user()->shopeeToken;

        if (!$hasShopeeToken) {
            return redirect()->route('shopee.connect');
        }

        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun tidak aktif'])->onlyInput('email');
        }

        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
