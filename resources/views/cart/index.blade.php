@extends('layouts.default')

@section('title', 'Shopping cart')

@section('content')
    <div class="bg-gray-100 p-4 grid grid-cols-5 gap-4">
        <div class="col-span-3 grid gap-4 content-start">
            <div class="bg-white p-4">
                <h1 class="text-2xl font-semibold">Shopping cart</h1>
                <p class="text-gray-500 text-lg">{{ $products->count() }} producten</p>

                <div class="grid gap-4 mt-4">
                    @foreach ($products as $product)
                        @include('cart.includes.cart-item', ['product' => $product])
                    @endforeach
                </div>
            </div>
            @include('cart.includes.delivery')
        </div>
        <div class="col-span-2 grid gap-4 content-start">
            @include('cart.includes.discount-code')
            <div class="bg-white p-4">
                <h1 class="text-2xl font-semibold">Totaal prijs</h1>
                <table class="w-full">
                    <tr>
                        <td class="py-4">Subtotaal:</td>
                        <td class="py-4 text-right">&euro; {{ $subtotal }}</td>
                    </tr>
                    <tr>
                        <td class="py-4">Verzending:</td>
                        <td class="py-4 text-right">&euro; {{ $shipping }}</td>
                    </tr>
                    {{-- Als kortingscode toon je het stukje hieronder; met de juiste gegevens --}}
                    @if (session()->has('discount_code'))
                        <tr>
                            <td class="py-4">
                                Kortingscode:
                                <span class="block text-gray-500">
                                    {{ session('discount_code') }} ({{ $discountAmount }}%)
                                    <a href="{{ route('discount.remove') }}" class=""><i
                                            class="fa-solid fa-circle-minus"></i></a>
                                </span>
                            </td>
                            <td class="py-4 text-right">- &euro;{{ $discountAmount }}</td>
                        </tr>
                    @endif
                    <tfoot class="border-t border-gray-200">
                        <tr>
                            <td class="py-4 font-semibold">Totaalprijs (inclusief BTW)</td>
                            <td class="py-4 font-semibold text-right">&euro; {{ $total }}</td>
                        </tr>
                    </tfoot>
                </table>

                @if (Auth::user()->cart()->count() > 0)
                    <a href="{{ route('checkout') }}"
                        class="mt-4 block hover:bg-orange-600 bg-orange-500 uppercase text-center font-semibold text-lg cursor-pointer text-white px-4 py-2 w-full">
                        Bestelling plaatsen
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
