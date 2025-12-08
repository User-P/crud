<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class OktaController extends Controller
{
    public function login(): Response
    {
        $samlEnabled = $this->samlEnabled();
        $idpName = config('saml2_settings.idpNames.0', 'okta');
        $settings = config('saml2.' . $idpName . '_idp_settings', []);
        $sp = $settings['sp'] ?? [];
        $idp = $settings['idp'] ?? [];

        return Inertia::render('Auth/Login', [
            'laravelVersion' => app()->version(),
            'auth' => [
                'driver' => $samlEnabled ? 'saml' : 'local',
                'login_url' => $samlEnabled ? route('saml.login') : route('login.perform'),
                'metadata_url' => $samlEnabled ? route('saml.metadata') : null,
                'sp' => [
                    'entity_id' => $sp['entityId'] ?? null,
                    'acs' => $sp['assertionConsumerService']['url'] ?? null,
                    'sls' => $sp['singleLogoutService']['url'] ?? null,
                ],
                'idp' => [
                    'entity_id' => $idp['entityId'] ?? null,
                    'sso' => $idp['singleSignOnService']['url'] ?? null,
                    'slo' => $idp['singleLogoutService']['url'] ?? null,
                ],
                'register_url' => $samlEnabled ? null : route('register'),
                'registration_enabled' => ! $samlEnabled,
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

    public function authenticate(Request $request): RedirectResponse
    {
        if ($this->samlEnabled()) {
            return redirect()->route('saml.login');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Credenciales inválidas o usuario no autorizado.']);
        }

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function showRegister(): Response|RedirectResponse
    {
        if ($this->samlEnabled()) {
            return redirect()->route('login')->with('error', 'El registro local está deshabilitado mientras Okta (SAML) esté activo.');
        }

        return Inertia::render('Auth/Register', [
            'laravelVersion' => app()->version(),
            'auth' => [
                'register_url' => route('register.perform'),
                'login_url' => route('login'),
            ],
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        if ($this->samlEnabled()) {
            return redirect()->route('login')->with('error', 'El registro local está deshabilitado mientras Okta (SAML) esté activo.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'employee_number' => ['nullable', 'string', 'max:255', 'unique:users,employee_number'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $employeeNumber = $data['employee_number'] ?? null;
        if ($employeeNumber === '') {
            $employeeNumber = null;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'employee_number' => $employeeNumber,
            'password' => $data['password'],
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard')->with('success', 'Cuenta creada y sesión iniciada correctamente.');
    }

    protected function samlEnabled(): bool
    {
        return (bool) config('saml2_settings.enabled', false);
    }
}
