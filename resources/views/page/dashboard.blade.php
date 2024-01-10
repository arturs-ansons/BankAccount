@extends('layout.default')

@section('dashboard')

    <div class="container mx-auto my-6 font-bold text-3xl text-left">
        <h1 class="text-gray-900 mb-4">Hello, {{ $firstname }} {{ $lastname }}!</h1>
        <a data-test="logout" href="{{ url('logout') }}" class="btn btn-primary inline-block mt-2 ml-auto">Logout</a>
    </div>

    <div class="container mx-auto text-left">
        @if ($eurBalance > 0)
            <p class="block text-sm font-medium text-gray-700 mt-2">Euro Balance: {{ $eurBalance }} EUR</p>
        @else
            <p class="block text-sm font-medium text-gray-700 mt-2">Euro Balance: 0.00 EUR</p>
        @endif

        @if ($usdBalance > 0)
            <p class="block text-sm font-medium text-gray-700 mt-2">Dollar Balance: {{ $usdBalance }} USD</p>
        @else
            <p class="block text-sm font-medium text-gray-700 mt-2">Dollar Balance: 0.00 USD</p>
        @endif

        @if ($invBalance > 0)
            <p class="block text-sm font-medium text-gray-700 mt-2">Investment Balance: {{ number_format($invBalance, 2) }} USD</p>
        @else
            <p class="block text-sm font-medium text-gray-700 mt-2">Investment Balance: 0.00 USD</p>
        @endif

        @if ($cryptoBalance > 0)
            <p class="block text-sm font-medium text-gray-700 mt-2">
                BTC Balance:
                <span style="color: {{ $btcCurrentPrice < $btcAvgPrice ? 'green' : 'red' }};">{{ $cryptoBalance }}</span>
            </p>
        @endif

        @if ($xrpBalance > 0)
            <p p class="block text-sm font-medium text-gray-700 mt-2">
                XRP Balance:
                <span>{{ $xrpBalance }}</span>
            </p>
        @endif

        @if ($ethBalance > 0)
            <p p class="block text-sm font-medium text-gray-700 mt-2">
                ETH Balance:
                <span>{{ $ethBalance }}</span>
            </p>
        @endif

        @if ($btcAvgPrice > 0)
            <p class="block text-sm font-medium text-gray-700 mt-2">BTC Purchase price: {{ $btcAvgPrice }}</p>
        @endif

        @if ($btcCurrentPrice > 0)
            <p class="block text-sm font-medium text-gray-700 mt-2">BTC Real-time price: {{ $btcCurrentPrice }}</p>
        @endif

        <p class="block text-sm font-medium mt-2">
            BTC price change: <span style="color: {{ $btcPercentage >= 0 ? 'green' : 'red' }}">{{ $btcPercentage }} %</span>
        </p>

        @if ($eurIban != null)
            <p class="block text-sm font-medium mt-2">EUR IBAN: {{ $eurIban }}</p>
        @endif

        @if ($usdIban != null)
            <p class="block text-sm font-medium mt-2">USD IBAN: {{ $usdIban }}</p>
        @endif

        @if ($invIban != null)
            <p class="block text-sm font-medium mt-2">INV IBAN: {{ $invIban }}</p>
        @endif

        @if(session('error'))
            <div style="color: red;">
                <br>
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div style="color: green;">
                <br>
                {{ session('success') }}
            </div>
        @endif

    </div>

@stop

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
