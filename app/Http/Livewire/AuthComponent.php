<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthComponent extends Component
{
    public $mode = 'login'; // login|register|password
    public $login;
    public $password;
    public $name;
    public $email;
    public $phone;
    public $password_confirmation;
    public $current_password;
    public $returnUrl;
    public $errorMessage;
    public $successMessage;

    public function mount($mode = 'login', $returnUrl = null)
    {
        $this->mode = $mode;
        $this->returnUrl = $returnUrl;
    }

    public function render()
    {
        return view('livewire.auth.auth-component');
    }

    public function login()
    {
        $validated = $this->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $customer = Customer::where('is_active', true)
            ->where('type', 'member')
            ->where(function ($query) use ($validated) {
                $query->where('email', $validated['login'])
                      ->orWhere('phone', $validated['login']);
            })
            ->first();

        if (! $customer || ! $customer->password || ! Hash::check($validated['password'], $customer->password)) {
            $this->errorMessage = 'Email/no HP atau password member tidak sesuai.';
            return;
        }

        session(['website_customer' => [
            'id' => $customer->id,
            'name' => $customer->name,
            'type' => $customer->type,
            'points_balance' => (int) ($customer->points_balance ?? 0),
        ]]);

        return redirect()->to($this->returnUrl ?: route('website.products.index'));
    }

    public function register()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email',
            'phone' => 'required|string|max:50|unique:customers,phone',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'Email sudah terdaftar.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'type' => 'member',
            'is_active' => true,
            'points_balance' => 0,
        ]);

        $this->ensureMemberCode($customer);

        session(['website_customer' => [
            'id' => $customer->id,
            'name' => $customer->name,
            'type' => $customer->type,
            'points_balance' => (int) ($customer->points_balance ?? 0),
        ]]);

        return redirect()->route('website.products.index')
            ->with('success', 'Registrasi berhasil. Selamat datang, '.$customer->name.'!');
    }

    public function updatePassword()
    {
        $validated = $this->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);

        $customer = $this->currentWebsiteCustomer();
        if (! $customer) {
            return redirect()->route('website.member.login', ['return' => request()->fullUrl()]);
        }

        if (! $customer->password || ! Hash::check($validated['current_password'], $customer->password)) {
            $this->errorMessage = 'Password lama tidak sesuai.';
            return;
        }

        $customer->update(['password' => $validated['password']]);
        return redirect()->to($this->returnUrl ?: route('website.products.index'))
            ->with('success', 'Password berhasil diperbarui.');
    }

    public function submit()
    {
        if ($this->mode === 'login') {
            return $this->login();
        } elseif ($this->mode === 'register') {
            return $this->register();
        } elseif ($this->mode === 'password') {
            return $this->updatePassword();
        }
    }

    private function currentWebsiteCustomer(): ?Customer
    {
        $session = session('website_customer');
        $id = is_array($session) ? (int) ($session['id'] ?? 0) : 0;
        if ($id <= 0) {
            return null;
        }
        $customer = Customer::find($id);
        if (! $customer || ! $customer->isMember()) {
            return null;
        }
        return $customer;
    }

    private function ensureMemberCode(Customer $customer): void
    {
        if (! empty($customer->member_code)) {
            return;
        }
        $code = 'MBR-'.str_pad((string) $customer->id, 6, '0', STR_PAD_LEFT);
        if (Customer::query()->where('member_code', $code)->where('id', '!=', $customer->id)->exists()) {
            $code .= '-'.random_int(10, 99);
        }
        $customer->update(['member_code' => $code]);
    }
}
