<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorChallengeController extends Controller
{
    /**
     * Pantalla donde el usuario ingresa el código de 2FA luego de autenticarse.
     */
    public function create(Request $request): RedirectResponse|Response
    {
        $challenge = $request->session()->get('two_factor');

        if (!$challenge || !isset($challenge['id'])) {
            return redirect()->route('login');
        }

        $user = User::find($challenge['id']);

        if (!$user || !$user->two_factor_secret) {
            return redirect()->route('login')->with('error', 'Inicia sesión nuevamente.');
        }

        return Inertia::render('Auth/TwoFactorChallenge', [
            'email' => $user->email,
        ]);
    }

    /**
     * Verifica el código ingresado y finaliza el proceso de login.
     */
    public function store(
        TwoFactorChallengeRequest $request,
        TwoFactorService $service
    ): RedirectResponse {
        $challenge = $request->session()->get('two_factor');

        if (!$challenge || !isset($challenge['id'])) {
            return redirect()->route('login')->with('error', 'La sesión del reto expiró. Intenta nuevamente.');
        }

        $user = User::find($challenge['id']);

        if (!$user || !$user->two_factor_secret) {
            return redirect()->route('login')->with('error', 'No fue posible validar al usuario.');
        }

        $validated = $request->validated();
        $passed = false;

        if (!empty($validated['code']) && $service->verify($user->two_factor_secret, $validated['code'])) {
            $passed = true;
        }

        if (!$passed && !empty($validated['recovery_code'])) {
            $normalizedCode = str_replace([' ', '-'], '', Str::upper($validated['recovery_code']));
            $codes = collect($user->two_factor_recovery_codes ?? []);
            $match = $codes->first(fn ($code) => hash_equals($code, $normalizedCode));

            if ($match) {
                $passed = true;
                $user->forceFill([
                    'two_factor_recovery_codes' => $codes->reject(fn ($code) => $code === $match)->values()->all(),
                ])->save();
            }
        }

        if (!$passed) {
            return back()->withErrors([
                'code' => 'El código o código de recuperación no es válido.',
            ]);
        }

        Auth::login($user, $challenge['remember'] ?? false);
        $request->session()->forget('two_factor');
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}
