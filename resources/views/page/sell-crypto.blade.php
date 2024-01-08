@extends('layout.default')

@section('content')
    <div class="flex justify-center items-center my-4" style="margin-right: 15px; margin-top: 20px">
        <form action="{{ route('sell-crypto') }}" method="post" class="mr-4">
            @csrf

            <br>
            <label for="amount" class="block text-lg font-medium text-gray-700 mt-2">Sell Crypto:</label>
            <input type="number" name="amount" step="0.00000001" min="0.00000001" required class="form-input">
            <div class="mt-4">
                <label for="sellCurrency">Receive Currency:</label>
                <select name="sellCurrency" id="sellCurrency" class="form-select" style="width: 207px">
                    <option value="default">Currency options</option>
                    <option value="btc">Bitcoin (BTC)</option>
                    <option value="eth">Ethereum (ETH)</option>
                    <option value="xrp">Ripple (XRP)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Sell Crypto</button>
        </form>
    </div>
@endsection
