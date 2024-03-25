<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use App\Notifications\InvoicePaid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Notifications\OnTheWayNotification;

class OrdersController extends Controller
{

function checkout() {
        return view('orders.checkout');
    }

    public function store(Request $request ,Order $order) {
        // Valideer het formulier zodat alle velden verplicht zijn.
        // Vul het formulier terug in, en toon de foutmeldingen.

        //$user = Auth::user();

        $request->validate([
            'voornaam' => 'required',
            'achternaam' => 'required',
            'straat' => 'required',
            'huisnummer' => 'required',
            'postcode' => 'required',
            'woonplaats' => 'required',

        ]);

        // Maak een nieuw "order" met de gegevens uit het formulier in de databank
        // Zorg ervoor dat hett order gekoppeld is aan de ingelogde gebruiker.

        $order = new Order();
        $order->voornaam = $request->voornaam;
        $order->achternaam = $request->achternaam;
        $order->straat= $request->straat;
        $order->huisnummer = $request->huisnummer;
        $order->postcode = $request->postcode;
        $order->woonplaats = $request->woonplaats;


        $order->created_at = Carbon::now();
        $order->updated_at = Carbon::now();

        $order->user_id = Auth::id();
        $order->save();


        $user = Auth::user();
        $user->notify(new InvoicePaid($order));

        // Zoek alle producten op die gekoppeld zijn aan de ingelogde gebruiker (shopping cart)

        $products = auth()->user()->cart;
        // Overloop alle gekoppelde producten van een user (shopping cart)

        foreach($products as $product){

            $order->products()->attach($product->id, [
                'quantity' => $product->pivot->quantity,
                'size' => $product->pivot->size
            ]);
        }
            // Attach het product, met bijhorende quantity en size, aan het order
            // https://laravel.com/docs/9.x/eloquent-relationships#retrieving-intermediate-table-columns
            // Detach tegelijk het product van de ingelogde gebruiker zodat de shopping cart terug leeg wordt

            auth()->user()->cart()->detach($product->id);

        // BONUS: Als er een discount code in de sessie zit koppel je deze aan het discount_code_id in het order model
        // Verwijder nadien ook de discount code uit de sessie

        if (session()->has('discount_code')) {
            $discountCode = DiscountCode::where('code', session('discount_code'))->first();
            if ($discountCode) {
                $order->discount_code_id = $discountCode->id;
                $order->save();
                // Remove the discount code from the session
                session()->forget('discount_code');
            }
        }


        // BONUS: Stuur een e-mail naar de gebruiker met de melding dat zijn bestelling gelukt is,
        // samen met een knop of link naar de show pagina van het order


        // Redirect naar de show pagina van het order en pas de functie daar aan
        return redirect()->route('orders.show', $order->id);
    }

    public function index() {

        // Zoek alle orders van de ingelogde gebruiker op. Vervang de "range" hieronder met de juiste code
        $orders = Auth::user()->orders()->get();


        // Pas de views aan zodat de juiste info van een order getoond word in de "order" include file
        return view('orders.index', [
            'orders' => $orders,

        ]);
    }

    public function show(Order $order) { // Order $order
        // Beveilig het order met een GATE zodat je enkel jouw eigen orders kunt bekijken.

        if (! Gate::allows('view-order', $order)) {
                abort(403);
             }

        $orderDate = Carbon::parse($order->created_at)->translatedFormat('j F Y');

        // In de URL wordt het id van een order verstuurd. Zoek het order uit de url op.
        // Zoek de bijbehorende producten van het order hieronder op.

       // $products = Product::take(4)->get();

       $products = $order->products()->get();

       $discountCode = null;
       if ($order->discount_code_id) {
           $discountCode = DiscountCode::find($order->discount_code_id);
       }

       foreach ($products as $product) {
        $product->total_price_with_discount = $product->price * $product->pivot->quantity;

        // Als er een korting van toepassing is, pas deze toe op de totale prijs van het product.
        if ($discountCode) {
            $product->total_price_with_discount -= ($product->total_price_with_discount * $discountCode->discount) / 100;
        }
    }

       //$discountCode = $order->user->discountCodes()->where('code', $order->discount_code_id)->first();

        // Geef de juiste data door aan de view
        // Pas de "order-item" include file aan zodat de gegevens van het order juist getoond worden in de website
        return view('orders.show', [
            'order' => $order,
            'orderDate' => $orderDate,
            'products' => $products,
            'discountCode' => $discountCode
        ]);
    }
}
