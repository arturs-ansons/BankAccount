@extends('layout.default')

@section('content')
    <div class="container mx-auto my-6 font-bold text-3xl text-center">
        <h1 class="text-gray-900 mb-4">Hello, {{ $firstname }} {{ $lastname }}!</h1>
        <div class="text-right">
            <a data-test="logout" href="{{ url('logout') }}" class="btn btn-primary inline-block mt-2 ml-auto">Logout</a>
        </div>
    </div>

    <div class="container mx-auto text-center">
        @if ($eurBalance > 0)
            <p class="block text-sm font-medium text-gray-700 mt-2">Euro Balance: {{ $eurBalance }} EUR</p>
        @endif

        @if ($usdBalance > 0)
            <p class="block text-sm font-medium text-gray-700 mt-2">Dollar Balance: {{ $usdBalance }} USD</p>
        @endif

        @if ($invBalance > 0)
            <p class="block text-sm font-medium text-gray-700 mt-2">Investment Balance: {{ $invBalance }} USD</p>
        @endif
            @if ($cryptoBalance > 0)
                <p class="block text-sm font-medium">
                    BTC Balance:
                    <span style="color: {{ $btcCurrentPrice < $btcAvgPrice ? 'green' : 'red' }};">{{ $cryptoBalance }}</span></p>
            @endif
            @if ($btcAvgPrice > 0)
                <p class="block text-sm font-medium text-gray-700 mt-2">BTC Purchase price: {{ $btcAvgPrice }}</p>
            @endif
            @if ($btcCurrentPrice > 0)
                <p class="block text-sm font-medium text-gray-700 mt-2">BTC Real time price: {{ $btcCurrentPrice }}</p>
            @endif
            <p class="block text-sm font-medium mt-2">
                BTC price change: <span style="color: {{ $btcPercentage >= 0 ? 'green' : 'red' }}">{{ $btcPercentage }} %</span>
            </p>




    </div>

    <div class="flex justify-center items-center my-4">
        <form action="{{ route('transfer-money') }}" method="post" class="mr-4">
            @csrf
            <label for="accountNr" class="block text-lg font-medium text-gray-700 mt-2">
                Recipient Bank Account:
            </label>
            <input type="number" name="accountNr" required class="form-input">

            <label for="amount">Amount:</label>
            <input type="number" name="amount" step="0.01" min="0.01" required class="form-input">

            <div class="mt-4">
                <label for="transferCurrency">Transfer Currency:</label>
                <select name="transferCurrency" id="transferCurrency" class="form-select" style="width: 207px">
                    <option value="default">Account options</option>
                    <option value="eur">Euro (EUR)</option>
                    <option value="usd">Dollar (USD)</option>
                    <option value="inv">Investment Acc (EUR)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Transfer Money</button>
            </br>
        </form>
    </div>

    <div class="flex justify-center items-center my-4 border-t pt-4" style="margin-right: 15px;">
        <form action="{{ route('add-currency') }}" method="post">
            @csrf
            </br>
            <label for="newCurrency" class="block text-lg font-medium text-gray-700 mt-2">Add New Currency Account:</label>
            <select name="newCurrency" id="newCurrency" class="form-select" style="width: 207px">
                <option value="default">Account options</option>
                <option value="eur">Euro (EUR)</option>
                <option value="usd">Dollar (USD)</option>
                <option value="inv">Investment (EUR)</option>
                <option value="btc">BTC</option>
            </select>
            <button type="submit" class="btn btn-primary mt-4">Add Currency</button>
        </form>
    </div>
    </br>
    <div class="flex justify-center items-center my-4 border-t pt-4" style="margin-right: 15px;">
        <form action="{{ route('buy-crypto') }}" method="post">
            @csrf
            </br>
            <label for="amount" class="block text-lg font-medium text-gray-700 mt-2">Buy BTC:</label>
            <input type="number" name="amount" step="0.01" min="0.01" required class="form-input">

            <div class="mt-4">
                <label for="buyCurrency">Payment Currency:</label>
                <select name="buyCurrency" id="buyCurrency" class="form-select" style="width: 207px">
                    <option value="default">Currency options</option>
                    <option value="inv">Dollar (USD)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Buy BTC</button>
        </form>
    </div>
@stop
