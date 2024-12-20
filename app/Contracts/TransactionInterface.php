<?php

namespace App\Contracts;

interface TransactionInterface
{
    public function createTransaction(array $data);

    public function getAllTransactions($id);
    public function createTransactionIp(array $data);

    public function getAllTransactionIps($id, $ip);

    public function getTransactionWithinTime($id, $start, $end);
}
