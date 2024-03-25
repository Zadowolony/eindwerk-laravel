<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function login() {
        return view('auth.login');
    }

    public function handleLogin(Request $request) {
        // Valideer het formulier
        // Elk veld is verplicht
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Schrijf de aanmeld logica om in te loggen.

        if(Auth::attempt($validated)){


            // Als je ingelogd bent stuur je de bezoeker door naar de intented "profile" route (zie hieronder)
            return redirect()->intended(route('profile'));
        }


        // Als je gegevens fout zijn stuur je terug naar het formulier met
        // een melding voor het email veld dat de gegevens niet correct zijn.
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.'
        ]);

    }

    public function register() {
        return view('auth.register');
    }

    public function handleRegister(Request $request) {
        // Valideer het formulier.

        $request->validate([
            'name' => 'required',
            'email' => 'required|email:rfc,filter,dns|unique:users,email',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);



        // Elk veld is verplicht / Wachtwoord en confirmatie moeten overeen komen / Email adres moet uniek zijn
        // Bewaar een nieuwe gebruiker in de databank met een beveiligd wachtwoord.
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        // BONUS: Verstuur een email naar de gebruiker waarin staat dat er een nieuwe account geregistreerd is voor de gebruiker.

        event(new Registered($user));
        $user->sendEmailVerificationNotification();



        $request->session()->flash('success', 'Registration successful! A verification link has been sent to your email.');
        return redirect()->route('login');

       // return redirect()->route('auth.verify-email');
    }

    public function logout() {
        // Gebruiker moet uitloggen
        Auth::logout();
        return back();
    }
}