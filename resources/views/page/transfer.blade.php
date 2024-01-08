@extends('layout.default')

@section('dashboard')

<div class="flex justify-center items-center my-4" style="margin-right: 15px; margin-top: 20px">
    <form action="{{ route('transfer') }}" method="post" class="mr-4">
        @csrf
        <label for="accountNr" class="block text-lg font-medium text-gray-700 mt-2">
            Recipient Bank Account:
        </label>
        <input name="iban" required class="form-input">

        <label for="amount">Amount:</label>
        <input type="number" name="amount" step="0.01" min="0.01" required class="form-input">

        <div class="mt-4">
            <label for="transferCurrency">Transfer Currency:</label>
            <select name="transferCurrency" id="transferCurrency" class="form-select" style="width: 207px">
                <option value="default">Account options</option>
                <option value="eur">Euro (EUR)</option>
                <option value="usd">Dollar (USD)</option>
                <option value="inv">Investment Acc (USD)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-4">Transfer Money</button>
        </br>
    </form>
</div>
@stop
