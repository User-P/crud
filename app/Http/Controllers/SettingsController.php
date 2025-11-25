<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Settings/Index', [
            'authProvider' => [
                'name' => 'Okta',
                'domain' => config('services.okta.domain'),
                'scopes' => config('services.okta.scopes', 'openid profile email'),
            ],
            'user' => $request->user() ? [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ] : null,
        ]);
    }
}
