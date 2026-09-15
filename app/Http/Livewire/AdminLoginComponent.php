<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AdminLoginComponent extends Component
{
    public $login;
    public $password;
    public $remember = false;
    public $errorMessage;
    public $returnUrl;

    public function mount($returnUrl = null)
    {
        $this->returnUrl = $returnUrl;
    }

    public function render()
    {
        return view('livewire.auth.admin-login');
    }

    public function login()
    {
        $validated = $this->validate([
            'login' => 'required|string|max:255',
            'password' => ['required', Password::min(8)],
        ]);

        $credentials = [
            'email' => $validated['login'],
            'password' => $validated['password'],
        ];

        // Support login by username as well
        if (!filter_var($validated['login'], FILTER_VALIDATE_EMAIL)) {
            $credentials = [
                'username' => $validated['login'],
                'password' => $validated['password'],
            ];
        }

        if (Auth::attempt($credentials, $this->remember)) {
            $user = Auth::user();

            // Verify the user has an admin role (Owner, Manager, or Kasir)
            if (! $user->hasRole('Owner') && ! $user->hasRole('Manager') && ! $user->hasRole('Kasir')) {
                Auth::logout();
                $this->errorMessage = 'Anda tidak memiliki akses admin.';
                return;
            }

            return redirect()->intended($this->returnUrl ?: '/home');
        }

        $this->errorMessage = 'Email/nama pengguna atau password tidak sesuai.';
    }
}
