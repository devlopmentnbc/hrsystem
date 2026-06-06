<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Show Login Page
     */
    public function index()
    {
        AccessControl::ensureAdminSetup();

        return view('auth.login');
    }

    /**
     * Handle Login
     */
    public function authenticate(Request $request)
    {
        // Validate Form
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:4',
        ]);

        // Login Credentials
        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
            'is_active' => true,
        ];

        // Check Login
        if (Auth::attempt($credentials, $request->remember)) {

            // Regenerate Session
            $request->session()->regenerate();

            // Redirect Dashboard
            return redirect()->intended('/dashboard')
                ->with('success', 'Login successful');
        }

        // Login Failed
        return back()->withErrors([
            'email' => 'Invalid email or password',
        ])->onlyInput('email');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Logout successful');
    }
}