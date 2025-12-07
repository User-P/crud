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
        if (! config('saml2_settings.enabled')) {
            abort(503, 'SAML no está habilitado en este entorno. Define SAML_ENABLED=true.');
        }

        $idpName = config('saml2_settings.idpNames.0', 'okta');
        $settings = config('saml2.' . $idpName . '_idp_settings', []);
        $sp = $settings['sp'] ?? [];
        $idp = $settings['idp'] ?? [];

        return Inertia::render('Auth/Login', [
            'laravelVersion' => app()->version(),
            'auth' => [
                'driver' => 'saml',
                'login_url' => route('saml.login'),
                'metadata_url' => route('saml.metadata'),
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
}
