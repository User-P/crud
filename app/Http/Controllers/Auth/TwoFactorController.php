<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorConfirmRequest;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    /**
     * Genera un secreto y códigos de recuperación para iniciar el proceso de 2FA.
     */
    public function store(Request $request, TwoFactorService $service): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasEnabledTwoFactor()) {
            return back()->with('error', 'La autenticación de 2 factores ya está activa.');
        }

        $user->forceFill([
            'two_factor_secret' => $service->generateSecret(),
            'two_factor_recovery_codes' => $service->generateRecoveryCodes(),
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('success', 'Escanea el código QR con tu app de autenticación y confirma el código.');
    }

    /**
     * Confirma que el código ingresado es válido y activa definitivamente 2FA.
     */
    public function confirm(TwoFactorConfirmRequest $request, TwoFactorService $service): RedirectResponse
    {
        $user = $request->user();

        if (!$user->two_factor_secret) {
            return back()->with('error', 'Primero debes generar un secreto de autenticación.');
        }

        if (!$service->verify($user->two_factor_secret, (string) $request->code)) {
            return back()->withErrors([
                'code' => 'El código proporcionado no es válido. Intenta nuevamente.',
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return back()->with('success', 'Autenticación de 2 factores activada correctamente.');
    }

    /**
     * Desactiva 2FA y elimina los secretos almacenados.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('success', 'Autenticación de 2 factores desactivada.');
    }

    /**
     * Permite generar nuevos códigos de recuperación.
     */
    public function regenerateRecoveryCodes(Request $request, TwoFactorService $service): RedirectResponse
    {
        $user = $request->user();

        if (!$user->hasEnabledTwoFactor()) {
            return back()->with('error', 'Debes activar 2FA antes de regenerar códigos.');
        }

        $user->forceFill([
            'two_factor_recovery_codes' => $service->generateRecoveryCodes(),
        ])->save();

        return back()->with('success', 'Se generaron nuevos códigos de recuperación.');
    }
}
