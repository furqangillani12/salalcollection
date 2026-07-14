<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Shop\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function showLogin()    { return view('shop.auth.login'); }
    public function showRegister() { return view('shop.auth.register'); }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
            'remember' => 'sometimes|boolean',
        ]);

        $remember   = $request->boolean('remember');
        $loginInput = trim($data['login']);

        // Resolve the customer by email OR phone (digits-only, forgiving of
        // spaces/dashes/country code). Only accounts with a password set can
        // log in — admin enables login by setting one in the POS.
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL) !== false) {
            $customer = Customer::whereNotNull('password')
                ->where('email', $loginInput)->orderBy('id')->first();
        } else {
            // Match on the last 10 digits so +92 3xx / 03xx / 3xx all resolve
            // to the same local number regardless of how it was stored.
            $digits = preg_replace('/\D+/', '', $loginInput);
            $suffix = substr($digits, -10);
            $customer = strlen($suffix) < 7 ? null : Customer::whereNotNull('password')
                ->whereRaw("RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone,''),' ',''),'-',''),'(',''),')',''), 10) = ?", [$suffix])
                ->orderBy('id')->first();
        }

        if (!$customer || !Hash::check($data['password'], $customer->password)) {
            return back()->withErrors(['login' => 'Phone/email ya password ghalat hai (incorrect).'])->withInput();
        }

        Auth::guard('customer')->login($customer, $remember);
        $customer->update(['last_login_at' => now()]);
        $this->cart->mergeGuestIntoCustomer($customer->id);

        $request->session()->regenerate();
        return redirect()->intended(route('shop.account'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:191',
            'email'    => 'required|email|unique:customers,email',
            'phone'    => 'nullable|string|max:30',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $customer = Customer::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'phone'          => $data['phone'] ?? null,
            'password'       => $data['password'],
            'customer_type'  => 'customer',
            'loyalty_points' => 0,
            'credit_enabled' => false,
            'credit_limit'   => 0,
            'current_balance'=> 0,
            'credit_due_days'=> 30,
            'barcode'        => Customer::generateBarcode(),
        ]);

        Auth::guard('customer')->login($customer);
        $this->cart->mergeGuestIntoCustomer($customer->id);

        return redirect()->route('shop.account')->with('shop_success', 'Welcome to Almufeed!');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('shop.home');
    }
}
