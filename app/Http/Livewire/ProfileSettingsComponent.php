<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\UserLog;

class ProfileSettingsComponent extends Component
{
    public $current_password;
    public $password;
    public $password_confirmation;
    public $errorMessage;
    public $successMessage;

    public function mount()
    {
        // initialize empty values
        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function updatePassword()
    {
        $validated = $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'current_password.required' => 'Password lama tidak sesuai.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);

        $user = auth()->user();
        if (! $user || ! Hash::check($validated['current_password'], $user->password)) {
            $this->errorMessage = 'Password lama tidak sesuai.';
            return;
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        UserLog::log('CHANGE_OWN_PASSWORD', "User changed own password: {$user->name}", $user);

        $this->successMessage = 'Password berhasil diperbarui.';
        return $this->redirectRoute('profile.password.edit');
    }

    public function render()
    {
        return view('livewire.settings.profile-component');
    }
}
