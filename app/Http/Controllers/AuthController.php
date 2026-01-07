<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function registerForm()
    {
        return Inertia::render('Auth/Register');
    }

    public function loginForm(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->estatus_usuario !== 1 && $user->estatus_usuario_visualizador !== 1) { // Asegúrate de usar 'estatus' según la migración
                // Usuario inactivo, cerrar sesión y redirigir con mensaje
                Auth::logout();
                $request->session()->invalidate();

                throw ValidationException::withMessages([
                    'email' => 'Tu cuenta está inactiva, por favor contacta al administrador',
                ]);
            }

            // Establece el tiempo de inicio de sesión en la sesión
            session(['login_time' => Carbon::now()->format('Y-m-d H:i:s')]);

            $user = auth()->user()->load('roles.permisos');
            $roles = $user->roles;

            $rol = $roles->where('id_aplicacion', 1)->first();

            if ($user->estatus_usuario && !empty($rol)) {
                $redirectUrl = ($rol->rol == "Analista Investigador") ? '/seguimientos' : '/seleccion';
            } else {
                $redirectUrl = '/visualizador/indicadores-i3';
            }

            return redirect($redirectUrl);
        }

        // return Inertia::render('Auth/Login');
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:TBL_USUARIOS,email',
            'employee_number' => 'required|unique:TBL_USUARIOS,cve_empleado',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data = array(
            'nm_usuario' => $validatedData['name'],
            'email' => $validatedData['email'],
            'contrasena' => Hash::make($validatedData['password']),
            'estatus_usuario' => 0,
            'id_area' => 1,
            'cve_empleado' => $validatedData['employee_number']
        );

        User::insert($data);

        User::logTransaction((new User())->forceFill($data), 'INSERT');

        // Redirect to login page
        return redirect()->to('/login');
    }


    /**
     * Login an existing user.
     */

    public function login(Request $request)
    {
        // Valida los datos del request
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Intenta autenticar al usuario
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Usuario o contraseña incorrectos',
            ]);
        }

        $user = Auth::user();

        if ($user->estatus_usuario !== 1 && $user->estatus_usuario_visualizador !== 1) { // Asegúrate de usar 'estatus' según la migración
            // Usuario inactivo, cerrar sesión y redirigir con mensaje
            Auth::logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está inactiva, por favor contacta al administrador',
            ]);
        }

        // Establece el tiempo de inicio de sesión en la sesión
        session(['login_time' => Carbon::now()->format('Y-m-d H:i:s')]);

        $user = auth()->user()->load('roles.permisos');
        $roles = $user->roles;

        $rol = $roles->where('id_aplicacion', 1)->first();

        if ($user->estatus_usuario && !empty($rol)) {
            $redirectUrl = ($rol->rol == "Analista Investigador") ? '/seguimientos' : '/seleccion';
        } else {
            $redirectUrl = '/visualizador/indicadores-i3';
        }

        return response()->json(['redirect' => $redirectUrl]);
    }


    /**
     * Logout the current user.
     */
    public function logout(Request $request)
    {
        // Logout the user
        Auth::logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        // Redirect to the login page or homepage
        return redirect('/');
    }


    /**
     * Get the currently authenticated user.
     */
    public function userv1()
    {
        $user = auth()->user();
        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');
        $loginTime = session('login_time');

        return response()->json([
            'name' => $user->name,
            'loginTime' => $loginTime,
            'role' => $roles->first(),
            'permissions' => $permissions,
            'estatus_usuario' => $user->estatus_usuario,
            'estatus_usuario_visualizador' => $user->estatus_usuario_visualizador,
        ]);
    }

    public function user()
    {
        $user = auth()->user()->load('roles.permisos'); // Cargar las relaciones necesarias
        $roles = $user->roles;

        $rol = $roles->where('id_aplicacion', 1)->first();
        $rol_visualizador = $roles->where('id_aplicacion', 2)->first();


        // Obtener los permisos recorriendo los roles
        $permissions = $user->roles->flatMap(function ($role) {
            return $role->permisos->pluck('permiso');
        })->unique(); // Eliminar duplicados si un permiso está en más de un rol

        $loginTime = session('login_time');

        if ($user->estatus_usuario && !empty($rol)) {
            $redirectUrl = ($roles->first() == "Analista Investigador") ? '/seguimientos' : '/seleccion';
        } else {
            $redirectUrl = '/visualizador/indicadores-i3';
        }

        return response()->json([
            'id_usuario' => $user->id_usuario,
            'name' => $user->nm_usuario,
            'loginTime' => $loginTime,
            'role' => !empty($rol) ? $rol->rol : null,
            'role_visualizador' => !empty($rol_visualizador) ? $rol_visualizador->rol : null,
            'permissions' => $permissions->values(), // Asegurar que sea un array limpio
            'estatus_usuario' => $user->estatus_usuario,
            'estatus_usuario_visualizador' => $user->estatus_usuario_visualizador,
            'redirect' => $redirectUrl
        ]);
    }
}
