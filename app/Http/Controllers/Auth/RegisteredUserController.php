<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration screen.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'laravelVersion' => app()->version(),
        ]);
    }

    /**
     * Handle a new registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
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
}
