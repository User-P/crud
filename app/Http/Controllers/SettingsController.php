<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __invoke(Request $request, TwoFactorService $service): Response
    {
        $user = $request->user();

        $twoFactor = null;

        if ($user) {
            $hasSecret = (bool) $user->two_factor_secret;
            $isConfirmed = $user->hasEnabledTwoFactor();

            $twoFactor = [
                'enabled' => $isConfirmed,
                'pending' => $hasSecret && !$isConfirmed,
                'secret' => $hasSecret && !$isConfirmed ? $user->two_factor_secret : null,
                'qr_url' => $hasSecret && !$isConfirmed
                    ? $service->qrCodeUrl($user, $user->two_factor_secret)
                    : null,
                'recovery_codes' => $user->two_factor_recovery_codes ?? [],
                'confirmed_at' => optional($user->two_factor_confirmed_at)->toIso8601String(),
            ];
        }

        return Inertia::render('Settings/Index', [
            'twoFactor' => $twoFactor,
        ]);
    }
}
