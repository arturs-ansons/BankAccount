@extends('layout.default')

@section('add-currency')

    <div class="flex justify-center items-center my-4" style="margin-right: 15px; margin-top: 20px">
    <form action="{{ route('add-currency') }}" method="post">
        @csrf
        </br>
        <label for="newCurrency" class="block text-lg font-medium text-gray-700 mt-2">Add New Currency Account:</label>
        <select name="newCurrency" id="newCurrency" class="form-select" style="width: 207px">
            <option value="default">Account options</option>
            <option value="eur">Euro (EUR)</option>
            <option value="usd">Dollar (USD)</option>
            <option value="inv">Investment (USD)</option>
            <option value="btc">BTC</option>
            <option value="eth">ETH</option>
            <option value="xrp">XRP</option>
        </select>
        <button type="submit" class="btn btn-primary mt-4">Add Currency</button>
    </form>
</div>
</br>
@stop
