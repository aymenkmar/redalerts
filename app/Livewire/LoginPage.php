<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\SsoSetting;

class LoginPage extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $errorMessage = '';
    public $showPassword = false;
    public $canUseSSO = false;

    public function mount()
    {
        // Check if user is already authenticated and redirect to main dashboard
        if (Auth::check()) {
            return redirect('/main-dashboard');
        }

        // Initialize SSO check if email is already set
        if (!empty($this->email)) {
            $this->checkSSO();
        }
    }

    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function updatedEmail()
    {
        $this->checkSSO();
    }

    public function checkSSO()
    {
        if (!empty($this->email)) {
            $this->canUseSSO = SsoSetting::isEmailAllowed($this->email);
        } else {
            $this->canUseSSO = false;
        }
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
