<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorConfirmRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {
    }

    /**
     * Show the registration screen.
     */
    public function create(Request $request): Response
    {
        $pending = $request->session()->get('pending_registration');

        $twoFactorSetup = null;

        if ($pending) {
            $twoFactorSetup = [
                'email' => $pending['email'],
                'secret' => $pending['two_factor_secret'],
                'qr_url' => $this->twoFactorService->qrCodeUrl($pending['email'], $pending['two_factor_secret']),
                'recovery_codes' => $pending['two_factor_recovery_codes'],
            ];
        }

        return Inertia::render('Auth/Register', [
            'laravelVersion' => app()->version(),
            'twoFactorSetup' => $twoFactorSetup,
        ]);
    }

    /**
     * Handle a new registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $request->session()->forget('pending_registration');

        $enableTwoFactor = $request->boolean('enable_two_factor');

        if ($enableTwoFactor) {
            $secret = $this->twoFactorService->generateSecret();
            $codes = $this->twoFactorService->generateRecoveryCodes();

            $request->session()->put('pending_registration', [
                'name' => $request->string('name')->trim()->value(),
                'email' => $request->string('email')->lower()->value(),
                'password' => Hash::make($request->string('password')->value()),
                'role' => 'user',
                'two_factor_secret' => $secret,
                'two_factor_recovery_codes' => $codes,
            ]);

            return redirect()
                ->route('register')
                ->with('success', 'Confirma el código de tu app para completar el registro.');
        }

        $user = User::create([
            'name' => $request->string('name')->trim()->value(),
            'email' => $request->string('email')->lower()->value(),
            'password' => Hash::make($request->string('password')->value()),
            'role' => 'user',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }

    /**
     * Confirm the 2FA code before finalizing the registration.
     */
    public function confirmTwoFactor(TwoFactorConfirmRequest $request): RedirectResponse
    {
        $pending = $request->session()->get('pending_registration');

        if (!$pending) {
            return redirect()
                ->route('register')
                ->with('error', 'No se encontró un registro pendiente.');
        }

        $validated = $request->validated();

        if (!$this->twoFactorService->verify($pending['two_factor_secret'], (string) $validated['code'])) {
            return back()->withErrors([
                'code' => 'El código proporcionado no es válido. Intenta nuevamente.',
            ]);
        }

        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'password' => $pending['password'],
            'role' => $pending['role'],
            'two_factor_secret' => $pending['two_factor_secret'],
            'two_factor_recovery_codes' => $pending['two_factor_recovery_codes'],
            'two_factor_confirmed_at' => now(),
        ]);

        $request->session()->forget('pending_registration');

        event(new Registered($user));
        Auth::login($user);

        return redirect()->intended('/dashboard')
            ->with('success', 'Cuenta creada y autenticación en dos pasos activada.');
    }

    /**
     * Cancel the pending 2FA registration flow.
     */
    public function destroyPending(Request $request): RedirectResponse
    {
        $request->session()->forget('pending_registration');

        return redirect()
            ->route('register')
            ->with('success', 'Configuración de 2FA cancelada. Completa el formulario nuevamente.');
    }
}
