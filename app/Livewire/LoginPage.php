<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
            // Check directly using the same logic as the controller
            $allowedEmails = config('sso.allowed_emails', []);
            $allowedDomains = config('sso.allowed_domains', []);

            // Check exact email matches
            if (in_array($this->email, $allowedEmails)) {
                $this->canUseSSO = true;
                return;
            }

            // Check domain matches
            $domain = substr(strrchr($this->email, "@"), 1);
            $this->canUseSSO = in_array($domain, $allowedDomains);
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
