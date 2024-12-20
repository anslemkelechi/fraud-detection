<?php

namespace App\Contracts;

interface BlacklistInterface
{
    public function createBlacklistIp(array $data);

    public function getAllBlacklistedIps($id, $ip);

    public function getSingleBlacklistedIp($ip);

}
