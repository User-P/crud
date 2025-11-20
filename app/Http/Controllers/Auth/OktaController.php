<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OktaController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('Auth/Login', [
            'laravelVersion' => app()->version(),
            'okta' => [
                'authorize_url' => route('okta.redirect'),
                'domain' => config('services.okta.domain'),
            ],
        ]);
    }

    public function redirect(): RedirectResponse
    {
        $authorizeUrl = $this->buildAuthorizeUrl();

        return redirect()->away($authorizeUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = $request->session()->pull('okta_state');
        $state = $request->string('state')->toString();

        if (!$expectedState || !$state || !hash_equals($expectedState, $state)) {
            return redirect()->route('login')->with('error', 'La sesión de autenticación expiró. Intenta nuevamente.');
        }

        $code = $request->string('code')->toString();

        if (!$code) {
            return redirect()->route('login')->with('error', 'No se recibió el código de autorización.');
        }

        try {
            $tokens = $this->swapCodeForTokens($code);
            $accessToken = $tokens['access_token'] ?? null;

            if (! $accessToken) {
                throw new \RuntimeException('No access token returned by Okta.');
            }

            $profile = $this->fetchUserInfo($accessToken);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->with('error', 'No pudimos validar tu sesión con Okta.');
        }

        $user = $this->findOrCreateLocalUser($profile);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function buildAuthorizeUrl(): string
    {
        $state = Str::random(40);
        request()->session()->put('okta_state', $state);

        $params = [
            'client_id' => config('services.okta.client_id'),
            'response_type' => 'code',
            'scope' => config('services.okta.scopes', 'openid profile email'),
            'redirect_uri' => route('okta.callback'),
            'state' => $state,
        ];

        return sprintf('%s/oauth2/%s/v1/authorize?%s', $this->oktaBaseUrl(), $this->authorizationServer(), http_build_query($params));
    }

    protected function swapCodeForTokens(string $code): array
    {
        $response = Http::asForm()->post(sprintf('%s/oauth2/%s/v1/token', $this->oktaBaseUrl(), $this->authorizationServer()), [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.okta.client_id'),
            'client_secret' => config('services.okta.client_secret'),
            'redirect_uri' => route('okta.callback'),
            'code' => $code,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Token request failed: '.$response->body());
        }

        return $response->json();
    }

    protected function fetchUserInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get(sprintf('%s/oauth2/%s/v1/userinfo', $this->oktaBaseUrl(), $this->authorizationServer()));

        if (! $response->successful()) {
            throw new \RuntimeException('Userinfo request failed: '.$response->body());
        }

        return $response->json();
    }

    protected function findOrCreateLocalUser(array $profile): User
    {
        $email = $profile['email'] ?? ($profile['preferred_username'] ?? null);
        $email ??= ($profile['sub'] ?? Str::uuid()).'@no-email.local';
        $displayName = $profile['name'] ?? ($profile['preferred_username'] ?? $email);

        /** @var User|null $user */
        $user = User::where('okta_id', $profile['sub'] ?? '')
            ->orWhere('email', $email)
            ->first();

        $isNew = false;

        if (! $user) {
            $user = new User();
            $isNew = true;
        }

        $user->forceFill([
            'name' => $displayName ?? 'Usuario',
            'email' => $email,
            'okta_id' => $profile['sub'] ?? null,
            'profile_photo_url' => $profile['picture'] ?? null,
            'email_verified_at' => now(),
        ]);

        if ($isNew) {
            $user->password = Hash::make(Str::random(40));
            $user->role = $user->role ?? 'user';
        }

        $user->save();

        return $user;
    }

    protected function oktaBaseUrl(): string
    {
        return rtrim(config('services.okta.domain'), '/');
    }

    protected function authorizationServer(): string
    {
        return config('services.okta.authorization_server', 'default');
    }
}
