<?php

namespace App\Repository;

use App\Models\RiskWeights;
use App\Contracts\RiskInterface;

class RiskRepository implements RiskInterface
{

    public function createRiskWeight(array $data)
    {
        $riskWeight = RiskWeights::create($data);
        return $riskWeight;
    }

    public function getWeightById($id)
    {
        $riskWeight = RiskWeights::where('id', $id)->first();
        return $riskWeight;
    }

    public function updateWeight($id, array $data)
    {
        $riskWeight = RiskWeights::where('id', $id)->first();
        $riskWeight->identifier = $data['identifier'] || $riskWeight->identifier;
        $riskWeight->weight = $data['weight'] || $riskWeight->weight;
        $riskWeight->save();
        return $riskWeight;
    }

    public function getAllWeights()
    {
        $riskWeights = RiskWeights::all();
        return $riskWeights;
    }

    public function getWeightByIdentifier($identifier)
    {
        $riskWeight = RiskWeights::where('identifier', $identifier)->first();
        return $riskWeight;
    }
}
