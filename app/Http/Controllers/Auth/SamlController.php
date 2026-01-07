<?php

namespace App\Http\Controllers\Auth;

use Aacotroneo\Saml2\Saml2Auth as Saml2AuthService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class SamlController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $this->ensurePackageInstalled();

        $intended = $request->query('redirect', route('dashboard'));
        $request->session()->put('saml_intended', $intended);

        return $this->saml()->login($intended);
    }

    public function acs(Request $request): RedirectResponse
    {
        $this->ensurePackageInstalled();

        $saml2Auth = $this->saml();
        $errors = $saml2Auth->acs();

        if (! empty($errors)) {
            $errorParts = [];
            $rawError = $errors['error'] ?? $errors;

            if (is_array($rawError)) {
                $errorParts[] = implode(', ', $rawError);
            } elseif (is_string($rawError)) {
                $errorParts[] = $rawError;
            }

            if (! empty($errors['last_error_reason'])) {
                $errorParts[] = (string) $errors['last_error_reason'];
            }

            $message = $errorParts ? implode(' | ', $errorParts) : 'Error procesando la respuesta SAML.';

            return redirect()->route('login')->with('error', 'Errores SAML: ' . $message);
        }

        $samlUser = $saml2Auth->getSaml2User();
        $attributes = $samlUser->getAttributes();
        $employeeNumber = $this->firstAttribute(
            $attributes,
            config('saml2_settings.employee_number_attribute', 'employeeNumber')
        );

        if (! $employeeNumber) {
            return redirect()->route('login')->with('error', 'No se recibió el atributo Número de Empleado en la aserción.');
        }

        try {
            $user = $this->resolveLocalUser($employeeNumber, $attributes);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->with('error', 'No fue posible localizar al usuario en el directorio local.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('saml_name_id', $samlUser->getNameId());
        $request->session()->put('saml_session_index', $samlUser->getSessionIndex());

        return redirect()->intended($request->session()->pull('saml_intended', '/dashboard'));
    }

    public function metadata()
    {
        $this->ensurePackageInstalled();

        $metadata = $this->saml()->getMetadata();

        return response($metadata, 200, ['Content-Type' => 'application/xml']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->ensurePackageInstalled();

        $nameId = $request->session()->get('saml_name_id');
        $sessionIndex = $request->session()->get('saml_session_index');
        $returnTo = route('login');

        $request->session()->put('saml_logout_redirect', $returnTo);

        return $this->saml()->logout($returnTo, [], $nameId, $sessionIndex);
    }

    public function sls(Request $request): RedirectResponse
    {
        $this->ensurePackageInstalled();

        $redirectTo = $request->session()->pull('saml_logout_redirect', route('login'));
        $saml2Auth = $this->saml();
        $errors = $saml2Auth->sls($this->idpName());

        if (! empty($errors)) {
            abort(500, 'Errores en SAML SLS: ' . implode(', ', $errors));
        }

        Auth::logout();
        $request->session()->forget(['saml_name_id', 'saml_session_index']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to($redirectTo);
    }

    protected function resolveLocalUser(string $employeeNumber, array $attributes): User
    {
        $user = User::where('employee_number', $employeeNumber)->first();

        if (! $user) {
            $profile = $this->lookupDirectoryProfile($employeeNumber, $attributes);

            if (! $profile) {
                throw new \RuntimeException('El directorio/SailPoint no devolvió datos.');
            }

            $user = new User();
            $user->employee_number = $employeeNumber;
            $user->email = $profile['email'] ?? $employeeNumber . '@no-email.local';
            $user->name = $profile['name'] ?? 'Empleado ' . $employeeNumber;
            $user->role = $profile['role'] ?? 'user';
            $user->password = Hash::make(Str::random(40));
            $user->email_verified_at = now();
        } else {
            $user->email_verified_at = $user->email_verified_at ?: now();
        }

        $user->save();

        return $user;
    }

    protected function lookupDirectoryProfile(string $employeeNumber, array $attributes): ?array
    {
        // Sustituye esta lógica por una consulta real a tu BD o a SailPoint.
        $email = $this->firstAttribute($attributes, 'email') ?? $employeeNumber . '@no-email.local';
        $name = $this->firstAttribute($attributes, 'name') ?? 'Empleado ' . $employeeNumber;

        return [
            'email' => $email,
            'name' => $name,
            'role' => 'user',
        ];
    }

    protected function firstAttribute(array $attributes, string $key): ?string
    {
        $value = $attributes[$key][0] ?? null;

        return is_string($value) ? $value : null;
    }

    protected function ensurePackageInstalled(): void
    {
        if (! class_exists(Saml2AuthService::class)) {
            abort(500, 'Instala el paquete aacotroneo/laravel-saml2 antes de usar las rutas SAML.');
        }
    }

    protected function saml(): Saml2AuthService
    {
        $idpName = $this->idpName();
        $oneLoginAuth = Saml2AuthService::loadOneLoginAuthFromIpdConfig($idpName);

        return new Saml2AuthService($oneLoginAuth);
    }

    protected function idpName(): string
    {
        return config('saml2_settings.idpNames.0', 'okta');
    }
}
