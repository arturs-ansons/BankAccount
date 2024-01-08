@extends('layout.default')

@section('buy-crypto')
    <div class="flex justify-center items-center my-4" style="margin-right: 15px; margin-top: 20px">
        <form action="{{ route('buy-crypto') }}" method="post">
            @csrf
            </br>
            <label for="amount" class="block text-lg font-medium text-gray-700 mt-2">Buy Crypto:</label>
            <input type="number" name="amount" step="0.01" min="0.01" required class="form-input">
            <div class="mt-4">
                <label for="buyCurrency">Payment Currency:</label>
                <select name="buyCurrency" id="buyCurrency" class="form-select" style="width: 207px">
                    <option value="default">Select Cryptocurrency</option>
                    <option value="BTC">Bitcoin (BTC)</option>
                    <option value="ETH">Ethereum (ETH)</option>
                    <option value="XRP">Ripple (XRP)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Buy Crypto</button>
        </form>
    </div>
@stop
