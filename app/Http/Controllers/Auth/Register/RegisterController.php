<?php

namespace App\Http\Controllers\Auth\Register;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Register\RegisterRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     *
     * @return \Inertia\Response
     */
    public function showRegisterForm()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \App\Http\Requests\Auth\Register\RegisterRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(RegisterRequest $request)
    {
        // Backend logic will be implemented later
        // This is just the structure
        
        $validated = $request->validated();
        
        // Registration logic will go here
        // $user = User::create([
        //     'first_name' => $validated['first_name'],
        //     'last_name' => $validated['last_name'],
        //     'email' => $validated['email'],
        //     'phone' => $validated['phone'] ?? null,
        //     'password' => Hash::make($validated['password']),
        // ]);
        
        // Auth::login($user);
        
        // return redirect()->route('dashboard');
        
        return back()->with('success', 'Registration successful!');
    }
}

