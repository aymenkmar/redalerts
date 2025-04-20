<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Response;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // For GET requests to the API login endpoint, show a simple form
        if ($request->isMethod('get') && $request->is('api/*')) {
            return response()->json([
                'message' => 'Please use POST method for login with email and password parameters',
                'example' => [
                    'email' => 'admin@redalerts.tn',
                    'password' => 'your_password'
                ]
            ]);
        }

        // For POST requests, validate and authenticate
        if ($request->isMethod('post')) {
            // Process login request

            // Validate the request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Special handling for API requests
            if ($request->is('api/*') || $request->wantsJson()) {
                // Find the user by email
                $user = \App\Models\User::where('email', $request->email)->first();

                // Check if user exists and password is correct
                if ($user && Hash::check($request->password, $user->password)) {
                    // Create token without session
                    $token = $user->createToken('auth-token')->plainTextToken;

                    return response()->json([
                        'user' => $user,
                        'token' => $token,
                        'message' => 'Login successful'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'The provided credentials are incorrect.',
                        'errors' => [
                            'email' => ['The provided credentials are incorrect.']
                        ]
                    ], 401);
                }
            }

            // For web requests, use session authentication
            if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended('/dashboard');
            }

            // Authentication failed for web requests

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->except('password'));
        }

        // If we get here, it's an invalid request method
        if ($request->is('api/*') || $request->wantsJson()) {
            return response()->json([
                'message' => 'Method not allowed',
                'allowed_methods' => ['GET', 'POST']
            ], 405);
        }

        return abort(405, 'Method not allowed');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // For API requests, revoke the token if authenticated
        if ($request->is('api/*') || $request->wantsJson()) {
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
            }
            return response()->json(['message' => 'Logged out successfully']);
        }

        // For web requests, logout and redirect
        Auth::logout();

        // Only manipulate session if it exists
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/login');
    }

    /**
     * Show the dashboard
     */
    public function dashboard()
    {
        return view('dashboard');
    }

    /**
     * Test authentication
     */
    public function authTest()
    {
        return response()->json([
            'message' => 'You are authenticated!',
            'user' => Auth::user()
        ]);
    }
}
