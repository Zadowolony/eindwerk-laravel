<a href="{{ route('favorites.toggle', $product) }}" class="group text-2xl bg-white px-4 py-1">

    @if (Auth::check())
        @if (Auth::user()->favorites()->where('product_id', $product->id)->count())
            {{-- Product is a favorite, show filled heart --}}
            <i class="fas fa-heart text-red-500"></i>
        @else
            {{-- Product is not a favorite, show outline heart --}}
            <i class="far fa-heart"></i>
        @endif
    @else
        {{-- User is not logged in, show outline heart --}}
        <i class="far fa-heart"></i>
    @endif
</a>
