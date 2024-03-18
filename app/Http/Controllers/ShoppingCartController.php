<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShoppingCartController extends Controller
{
    public function index() {
        // Pas de "cart-item" include file aan zodat de "$product->pivot->quantity" in de formuliervalue ingevuld wordt
        // en de size ook met "$product->pivot->size" afgedrukt wordt.
        // Zorg ervoor dat je de juiste velden bij de relatie in het User model meegeeft (zie documentatie)
        // https://laravel.com/docs/9.x/eloquent-relationships#retrieving-intermediate-table-columns
        // Zorg ook dat de prijs berekening in het "cart-item" klopt.

        // Check if the user is authenticated
        $user = Auth::user();



        // Zoek de producten van de ingelogde gebruiker op.
        // $products = Product::take(4)->get();
        $products = $user->cart;

        $shipping = 3.9;
        // DOE DE BEREKENING ALS LAATSTE STAP
        // Gebruik de "products" relatie op het user model (en gegevens de pivot table) om de producten te overlopen
        // en de volledige prijs van de winkelkar te berekenen.
        $subtotal = 0;

        foreach($products as $product){
            $subtotal += $product->price * $product->pivot->quantity;
        }



        // Bereken de verzendkosten van 3.9eur bij het totaal
        $total = $subtotal + $shipping;

        // BONUS: Als de kortingscode bestaat in de sessie, zoek deze op in de databank en pas de korting toe op de berekening.

        if(session()->has('discount_code')){

            $discountCode = DiscountCode::where('code', session('discount_code'))->first();

            if($discountCode){
                $discountAmount = ($subtotal * $discountCode->percentage) / 100;
                $total -= $discountAmount;
            }
        }
        // De kortingscode kan je dan ook naar de view hieronder doorsturen.
        // In de index view hieronder kan je dan ook het stukje in commentaar code tonen met de juiste gegegevens.
        // Indien er al een code ingevuld is zet je de input in de discount-code view file op "disabled"
        $discountAmount = DiscountCode::where('code', 'discount')->first();
        $discountCode = false;

        return view('cart.index', [
            'products' => $products,
            'shipping' => $shipping,
            'subtotal' => $subtotal,
            'total' => $total,
            'discountCode' => isset($discountCode) ? $discountCode : null,
            'discountAmount' => isset($discountAmount) ? $discountAmount : 0
        ]);
    }

    public function add(Request $request, Product $product) {
        // Voeg een controle query in zodat je elk product_id maar 1 keer aan de cart kan toevoegen
        $request->validate([
            'size' => 'required',
            'quantity' => 'required|numeric|min:1', // Assuming quantity should be numeric and at least 1
        ]);
        // "Attach" het product aan de ingelogde gebruiker
        // De size en quantity gegevens uit het formulier voeg je toe aan de "pivot" table (zie documentatie link)
        // https://laravel.com/docs/9.x/eloquent-relationships#attaching-detaching

         // Haal de ingelogde gebruiker op
    $user = Auth::user();

    // dd($request->all());

    $existingProduct = $user->cart()->where('product_id', $product->id)->where('size', $request->size)->exists();

    if ($existingProduct ) {

        return redirect()->back()->withErrors(['product_already_added' => 'You have already added this product to your cart.']);
    }

        $user->cart()->attach($product->id, [
            'quantity' => $request->quantity,
            'size' => $request->size
        ]);

        return redirect()->route('cart');
    }

    public function delete(Product $product, Request $request) {
        // "Detach" het product van de ingelogde gebruiker
        //

        $user = Auth::user();
        $size = $request->input('pivot.size');
        $user->cart()->where('product_id', $product->id)->wherePivot('size', $size)->detach();

       //$user->cart()->where('product_id', $product->id)->detach($product->id);

        return redirect()->route('cart');
    }

    public function update(Request $request, Product $product) {
        // Update de gegevens van de pivot table met het product id
        // https://laravel.com/docs/9.x/eloquent-relationships#updating-a-record-on-the-intermediate-table
        $request->validate([
            'quantity' => 'required|numeric|min:1',
        ]);
        $user = Auth::user();

        $user->cart()->updateExistingPivot($product->id, [
            'quantity' => $request->quantity,
        ]);

        return redirect()->route('cart');
    }


    /**
     * BONUS: DISCOUNTS
     */

    public function setDiscountCode(Request $request) {
        // Valideer het formulier (veld is verplicht) en vul het terug in bij foutmeldingen
        $request->validate([
            'code' => 'required'
        ]);

        //$request->session()->put('discount_codes', ['DISCOUNT20', 'HAPPY10', 'JUSTDOIT']);
        $discountCode = DiscountCode::where('code', strtoupper($request->code))->first();

        // Als de discount code gevonden is, sla deze op in de sessie
        if ($discountCode) {
            $request->session()->put('discount_code', $discountCode->code);
            return redirect()->route('cart')->with('success', 'Discount code successfully applied.');
        } else {
            // Als de discount code niet gevonden werd: ga terug met een foutmelding dat de code niet gevonden kon worden
            return redirect()->back()->with('error', 'Invalid discount code. Please try again.');
        }

        // BONUS
        // Zoek de discount code in de databank op die het CODE veld uit de request
        // Als de discount code gevonden werd:


            // Save de discount code naar de sessie zodat je deze later kan gebruiken bij checkout
            // https://laravel.com/docs/9.x/session#storing-data
        return redirect()->route('cart');

        // Als de discount code niet gevonden werd: ga terug met een foutmelding dat de code niet gevonden kon worden

    }

    public function removeDiscountCode() {
        // Verwijder de discount code uit de sessie

        return back();
    }
}