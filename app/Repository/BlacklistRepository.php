<?php

namespace App\Repository;

use App\Models\BlacklistedIps;
use App\Contracts\BlacklistInterface;


class BlacklistRepository implements BlacklistInterface
{
    public function createBlacklistIp(array $data)
    {
        $blacklist = BlacklistedIps::create($data);
        return $blacklist;
    }

    public function getAllBlacklistedIps($id, $ip)
    {
        $blacklists = BlacklistedIps::where('user_id', $id)
            ->where('ip_address', $ip)
            ->exists();
        return $blacklists;
    }

    public function getSingleBlacklistedIp($ip)
    {
        $blacklists = BlacklistedIps::where('ip_address', $ip)->first();
        return $blacklists;
    }
}
