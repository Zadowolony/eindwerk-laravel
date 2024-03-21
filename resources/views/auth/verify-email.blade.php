@extends('layouts.default')

@section('title', 'verfification')

@section('content')
    <section>

        <div class="max-w-md mx-auto bg-white rounded-md shadow-md overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl text-center font-bold text-gray-800 mb-4">Email Verification</h1>
                <p class="text-gray-700 text-center mb-6">Thank you for registering! To complete your registration, please
                    verify your email address </p>
                {{-- <div class="flex justify-center">
                    <a href="{{ route('verification.notice') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Verify
                        Email Address</a>
                </div> --}}
                <p class="text-center text-gray-700 mt-4">If you did not create an account, no further action is required.
                </p>
            </div>
            <div class="bg-gray-100 p-4 text-center text-gray-500">
                <p>&copy; {{ date('Y') }} Awesome Shoestore. All rights reserved.</p>
            </div>
        </div>

    </section>
@endsection
