<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;

class TransactionService
{
    public function getUserTransactions(User $user, int $perPage = 10)
    {
        return Transaction::where('user_id', $user->id)
            ->orWhere('recipient_id', $user->id)
            ->with(['balance', 'recipient'])
            ->paginate($perPage);
    }

}
