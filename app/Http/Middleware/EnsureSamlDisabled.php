<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureSamlDisabled
{
    /**
     * Solo permite el acceso cuando SAML/Okta está deshabilitado.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (config('saml2_settings.enabled', false)) {
            return redirect()
                ->route('login')
                ->with('error', 'El registro y el login local están deshabilitados mientras Okta (SAML) esté activo.');
        }

        return $next($request);
    }
}
