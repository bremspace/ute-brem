<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class WebsiteCustomerAuthController extends Controller
{
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

    public function loginForm(Request $request)
    {
        return view('website.auth.login', [
            'returnUrl' => $request->query('return', route('website.products.index')),
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string',
            'return' => 'nullable|string',
        ]);

        $customer = Customer::where('is_active', true)
            ->where('type', 'member')
            ->where(function ($query) use ($validated) {
                $query->where('email', $validated['login'])
                    ->orWhere('phone', $validated['login']);
            })
            ->first();

        if (! $customer || ! $customer->password || ! Hash::check($validated['password'], $customer->password)) {
            return back()
                ->withErrors(['login' => 'Email/no HP atau password member tidak sesuai.'])
                ->withInput($request->only('login', 'return'));
        }

        session(['website_customer' => [
            'id' => $customer->id,
            'name' => $customer->name,
            'type' => $customer->type,
            'points_balance' => (int) ($customer->points_balance ?? 0),
        ]]);

        return redirect()->to($validated['return'] ?: route('website.products.index'));
    }

    public function logout()
    {
        session()->forget('website_customer');

        return redirect()->route('website.products.index');
    }

    public function passwordForm(Request $request)
    {
        $customer = $this->currentWebsiteCustomer();
        if (! $customer) {
            return redirect()->route('website.member.login', [
                'return' => $request->fullUrl(),
            ]);
        }

        return view('website.auth.password', [
            'customer' => session('website_customer'),
            'returnUrl' => $request->query('return', route('website.products.index')),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $customer = $this->currentWebsiteCustomer();
        if (! $customer) {
            return redirect()->route('website.member.login', [
                'return' => $request->fullUrl(),
            ]);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
            'return' => 'nullable|string',
        ], [
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);

        if (! $customer->password || ! Hash::check($validated['current_password'], $customer->password)) {
            return back()
                ->withErrors(['current_password' => 'Password lama tidak sesuai.'])
                ->withInput($request->only('return'));
        }

        // Customer model already has hashed cast for password.
        $customer->update([
            'password' => $validated['password'],
        ]);

        return redirect()
            ->to($validated['return'] ?: route('website.products.index'))
            ->with('success', 'Password berhasil diperbarui.');
    }
}
