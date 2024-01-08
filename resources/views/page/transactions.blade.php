@extends('layout.default')

@section('transactions')
    <div class="flex justify-center items-center my-4">
        <table class="border-collapse border rounded-lg w-3/4">
            <thead>
            <tr class="bg-gray-200">
                <th class="border p-3">IBAN</th>
                <th class="border p-3">Amount</th>
                <th class="border p-3">Type</th>
                <th class="border p-3">Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($transactions as $transaction)

                <tr>
                    <td class="border p-3">{{ $transaction->iban }}</td>
                    <td class="border p-3">{{ $transaction->amount }}</td>
                    <td class="border p-3">{{ $transaction->type }}</td>
                    <td class="border p-3">{{ $transaction->created_at }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="border-b"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
<br>
    <div class="flex justify-center items-center my-4">
        {{ $transactions->links() }}
    </div>
@stop
