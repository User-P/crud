<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OktaController extends Controller
{
    public function login(): Response
    {
        $samlEnabled = $this->samlEnabled();
        $registrationEnabled = ! $samlEnabled;
        $authPayload = [
            'driver' => $samlEnabled ? 'saml' : 'local',
            'login_url' => $samlEnabled ? route('saml.login') : route('login.perform'),
            'metadata_url' => $samlEnabled ? route('saml.metadata') : null,
            'register_url' => $registrationEnabled ? route('register.perform') : null,
            'registration_enabled' => $registrationEnabled,
        ];

        return Inertia::render('Auth/Login', [
            'laravelVersion' => app()->version(),
            'auth' => array_merge($authPayload, $this->samlMetadata()),
        ]);
    }

    public function logout(Request $request)
    {
        if ($this->samlEnabled()) {
            $response = app(SamlController::class)->logout($request);

            if ($request->header('X-Inertia') && method_exists($response, 'getTargetUrl')) {
                return Inertia::location($response->getTargetUrl());
            }

            return $response;
        }

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

        $request->session()->regenerate();
        session(['login_time' => Carbon::now()->format('Y-m-d H:i:s')]);

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister(): Response|RedirectResponse
    {
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'employee_number' => ['nullable', 'string', 'max:255', 'unique:users,employee_number'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $employeeNumber = $validated['employee_number'] ?? null;
        if ($employeeNumber === '') {
            $employeeNumber = null;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_number' => $employeeNumber,
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        session(['login_time' => Carbon::now()->format('Y-m-d H:i:s')]);

        return redirect()->intended(route('dashboard'));
    }

    protected function samlEnabled(): bool
    {
        return (bool) config('saml2_settings.enabled', false);
    }

    protected function samlMetadata(): array
    {
        $idpName = config('saml2_settings.idpNames.0', 'okta');
        $settings = config('saml2.' . $idpName . '_idp_settings', []);
        $sp = $settings['sp'] ?? [];
        $idp = $settings['idp'] ?? [];

        return [
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
        ];
    }
}
