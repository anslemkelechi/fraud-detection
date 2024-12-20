<?php

namespace App\Contracts;

interface RiskInterface
{
    public function createRiskWeight(array $data);

    public function getWeightByIdentifier($identifier);

    public function getAllWeights();

    public function getWeightById($id);

    public function updateWeight($id, array $data);
}
