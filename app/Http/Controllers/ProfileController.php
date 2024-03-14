<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index() {
        // Pas de views aan zodat je de juiste item counts kunt tonen in de knoppen op de profiel pagina.
        $user = Auth::user();
        return view('profile.index', [
            'user' => $user
        ]);
    }

    public function edit() {
        // Vul het email adres van de ingelogde gebruiker in het formulier in
        $user = Auth::user();
        return view('profile.edit',[
            'user' => $user
        ]);
    }

    public function updateEmail(Request $request) {
        // Valideer het formulier, zorg dat het terug ingevuld wordt, en toon de foutmeldingen
        // Emailadres is verplicht en moet uniek zijn (behalve voor het huidge id van de gebruiker).
        // https://laravel.com/docs/9.x/validation#rule-unique -> Forcing A Unique Rule To Ignore A Given ID
        // Update de gegevens van de ingelogde gebruiker

        $user= $request->user();

        $request->validate([
            // 'email' => 'required|email|unique:users'
            'email' => [
                'required',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);


        $user->email = $request->email;
        $user->save();
        // return back()->with([
        //     'email' => $request->email
        // ]);
        // BONUS: Stuur een e-mail naar de gebruiker met de melding dat zijn e-mailadres gewijzigd is.
        $request->session()->flash('success_email', 'Email successfully updated.');
        return redirect()->route('profile.edit');
    }

    public function updatePassword(Request $request) {
        // Valideer het formulier, zorg dat het terug ingevuld wordt, en toon de foutmeldingen
        $user= $request->user();
        $request->validate([
            'password' => [
                'required',
                Rule::unique('users')->ignore($user->id),
            ],

        ]);

        $user->password = Hash::make($request->password);
        $user->save();
        // Wachtwoord is verplicht en moet confirmed zijn.
        // Update de gegevens van de ingelogde gebruiker met het nieuwe "hashed" password

        // BONUS: Stuur een e-mail naar de gebruiker met de melding dat zijn wachtwoord gewijzigd is.
        $request->session()->flash('success_password', 'Password successfully updated.');
        return redirect()->route('profile.edit');
    }
}