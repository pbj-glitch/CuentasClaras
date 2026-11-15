<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credenciales, remember: true)) {
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no coinciden.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('grupos.index'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $user = User::create($datos);
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('grupos.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
