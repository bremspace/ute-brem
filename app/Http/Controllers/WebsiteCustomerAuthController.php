<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPointLedger;
use App\Models\OnlineOrder;
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

    public function registerForm()
    {
        return view('website.member.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
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

    public function orders()
    {
        $customer = $this->currentWebsiteCustomer();
        if (! $customer) {
            return redirect()->route('website.member.login', ['return' => request()->fullUrl()]);
        }

        $orders = OnlineOrder::with('items')
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        return view('website.member.orders', [
            'orders' => $orders,
            'customer' => session('website_customer'),
        ]);
    }

    public function points()
    {
        $customer = $this->currentWebsiteCustomer();
        if (! $customer) {
            return redirect()->route('website.member.login', ['return' => request()->fullUrl()]);
        }

        $pointsHistory = CustomerPointLedger::where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($ledger) => (object) [
                'description' => $ledger->notes ?: ($ledger->source ?: 'Poin'),
                'points' => (int) $ledger->points,
                'created_at' => $ledger->created_at,
            ]);

        return view('website.member.points', [
            'customer' => $customer,
            'pointsHistory' => $pointsHistory,
        ]);
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
