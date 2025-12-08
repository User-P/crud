<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OktaController extends Controller
{
    public function login(): Response
    {
        $idpName = config('saml2_settings.idpNames.0', 'okta');
        $settings = config('saml2.' . $idpName . '_idp_settings', []);
        $sp = $settings['sp'] ?? [];
        $idp = $settings['idp'] ?? [];

        return Inertia::render('Auth/Login', [
            'laravelVersion' => app()->version(),
            'auth' => [
                'driver' => $this->samlEnabled() ? 'saml' : 'local',
                'login_url' => $this->samlEnabled() ? route('saml.login') : route('login.perform'),
                'metadata_url' => $this->samlEnabled() ? route('saml.metadata') : null,
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

    protected function samlEnabled(): bool
    {
        return (bool) config('saml2_settings.enabled', false);
    }
}
