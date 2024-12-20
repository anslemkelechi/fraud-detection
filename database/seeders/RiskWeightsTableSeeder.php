<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiskWeightsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $riskWeights = [
            ['identifier' => 'device_fingerprint', 'weight' => 20],
            ['identifier' => 'ip_address', 'weight' => 40],
            ['identifier' => 'transaction_amount', 'weight' => 20],
            ['identifier' => 'transaction_frequency', 'weight' => 20],
        ];


        foreach ($riskWeights as $weight) {
            DB::table('risk_weights')->insert([
                'identifier' => $weight['identifier'],
                'weight' => $weight['weight'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
