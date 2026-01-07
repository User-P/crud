<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OktaController extends Controller
{
    public function login(): Response
    {
        $samlEnabled = $this->samlEnabled();

        return Inertia::render('Auth/Login', [
            'auth' => [
                'driver' => $samlEnabled ? 'saml' : 'local',
                'login_url' => $samlEnabled ? route('saml.login') : route('login.perform'),
                'metadata_url' => $samlEnabled ? route('saml.metadata') : null,
            ],
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function authenticate(Request $request)
    {
        if ($this->samlEnabled()) {
            return redirect()->route('saml.login');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);


        // Intenta autenticar al usuario
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Usuario o contraseña incorrectos',
            ]);
        }

        $user = Auth::user()->load('roles.permisos');

        if ($user->estatus_usuario !== 1 && $user->estatus_usuario_visualizador !== 1) {
            Auth::logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está inactiva, por favor contacta al administrador',
            ]);
        }

        session(['login_time' => Carbon::now()->format('Y-m-d H:i:s')]);

        $roles = $user->roles;

        $rol = $roles->where('id_aplicacion', 1)->first();

        if ($user->estatus_usuario && !empty($rol)) {
            $redirectUrl = ($rol->rol == "Analista Investigador") ? '/seguimientos' : '/seleccion';
        } else {
            $redirectUrl = '/visualizador/indicadores-i3';
        }
        return redirect()->intended($redirectUrl);
    }

    public function showRegister(): Response|RedirectResponse
    {
        return Inertia::render('Auth/Register', [
            'auth' => [
                'register_url' => route('register.perform'),
                'login_url' => route('login'),
            ],
        ]);
    }

    protected function samlEnabled(): bool
    {
        return (bool) config('saml2_settings.enabled', false);
    }
}
