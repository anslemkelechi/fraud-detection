<?php

namespace App\Repository;

use App\Models\Transactions;
use App\Models\TransactionIps;
use App\Contracts\TransactionInterface;

class TransactionRepository implements TransactionInterface
{

    public function createTransaction(array $data)
    {
        $transaction = Transactions::create($data);
        return $transaction;
    }

    public function getAllTransactions($id)
    {
        $transactions = Transactions::where('user_id', $id)->get();
        return $transactions;
    }

    public function createTransactionIp(array $data)
    {
        $transaction = TransactionIps::create($data);
        return $transaction;
    }

    public function getAllTransactionIps($id, $ip)
    {
        $transactions = TransactionIps::where('user_id', $id)
            ->where('ip_address', $ip)
            ->exists();
        return $transactions;
    }

    public function getTransactionWithinTime($id, $start, $end)
    {
        $transactions =  Transactions::where('user_id', $id)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return $transactions;
    }
}
