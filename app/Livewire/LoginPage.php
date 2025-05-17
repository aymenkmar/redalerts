<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LoginPage extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $errorMessage = '';
    public $showPassword = false;

    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Use the existing Auth::attempt method
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended('/main-dashboard');
        }

        $this->errorMessage = 'The provided credentials do not match our records.';
    }

    public function render()
    {
        return view('livewire.login-page')
            ->layout('layouts.landing');
    }
}
