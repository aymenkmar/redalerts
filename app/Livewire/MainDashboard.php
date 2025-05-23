<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class MainDashboard extends Component
{
    public function render()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('livewire.main-dashboard')
            ->layout('layouts.main');
    }
}
