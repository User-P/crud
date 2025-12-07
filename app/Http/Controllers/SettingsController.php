<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $idpName = config('saml2_settings.idpNames.0', 'okta');
        $settings = config('saml2.' . $idpName . '_idp_settings', []);
        $sp = $settings['sp'] ?? [];
        $idp = $settings['idp'] ?? [];

        return Inertia::render('Settings/Index', [
            'authProvider' => [
                'name' => 'Okta (SAML)',
                'metadata_url' => config('saml2_settings.enabled') ? route('saml.metadata') : null,
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
            'user' => $request->user() ? [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ] : null,
        ]);
    }
}
