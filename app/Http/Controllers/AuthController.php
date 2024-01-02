<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index(): Response
    {
        return response()->view('page.account');
    }


    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/clientAccount');
        }

        return back();
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        return redirect('/');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'registerEmail' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', 'min:3'],
        ]);

        try {
            $user = User::create([
                'firstname' => $request->input('firstname'),
                'lastname' => $request->input('lastname'),
                'email' => $request->input('registerEmail'),
                'password' => bcrypt($request->input('password')),
            ]);
            $user->balances()->create([
                'account_type' => $request->input('currency'),
                'currency' => $request->input('currency'),
                //'balance' => 100,
            ]);

            Auth::login($user);

            return redirect()->route('clientAccount');

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
