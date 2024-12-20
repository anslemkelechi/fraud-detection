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

    public function getAllBlacklistedIps($id)
    {
        $blacklists = BlacklistedIps::where('user_id', $id)->get();
    }
}
