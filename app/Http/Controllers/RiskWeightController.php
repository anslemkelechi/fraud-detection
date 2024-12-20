<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\RiskInterface;
use App\Models\RiskWeights;
use Illuminate\Support\Facades\Validator;

class RiskWeightController extends Controller
{
    protected $riskRepository;

    public function __construct(RiskInterface $riskRepository)
    {
        $this->riskRepository = $riskRepository;
    }

    //Create new risk weight
    public function createRiskWeight(Request $request)
    {
        try {
            $identifier = $request->identifier;
            $weight = $request->weight;

            //Add Validations
            $validator = Validator::make($request->all(), [
                'identifier' => 'string',
                'weight' => 'integer',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return $this->respondWithError($validator->errors()->first(), 422);
            }

            $data = [
                "identifier" => $identifier,
                "weight" => $weight
            ];
            $riskWeight = $this->riskRepository->createRiskWeight($data);
            return $this->respondWithSuccess($riskWeight, 'Risk weight created successfully');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }

    //Update risk weight
    public function updateRiskWeight(Request $request)
    {
        try {
            $riskId = $request->route('riskId');
            $identifier = $request->identifier;
            $weight = $request->weight;

            //Add Validations
            $validator = Validator::make($request->all(), [
                'identifier' => 'string',
                'weight' => 'integer',

            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return $this->respondWithError($validator->errors()->first(), 422);
            }

            $data = [
                "identifier" => $identifier,
                "weight" => $weight
            ];
            //Find Weight
            $existingRiskWeight = $this->riskRepository->getWeightById($riskId);
            if (empty($existingRiskWeight)) {
                return $this->respondWithError('Risk weight not found', 404);
            }
            $riskWeight = $this->riskRepository->updateWeight($riskId, $data);
            return $this->respondWithSuccess($riskWeight, 'Risk weight updated successfully');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }

    //Get risk weight by identifier
    public function getRiskWeightByIdentifier(Request $request)
    {
        try {
            $identifier = $request->route("identifier");
            //Add Validations
            $validator = Validator::make($request->all(), [
                'identifier' => 'string',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return $this->respondWithError($validator->errors()->first(), 422);
            }

            //Find Weight
            $riskWeight = $this->riskRepository->getWeightByIdentifier($identifier);

            if (empty($riskWeight)) {
                return $this->respondWithError('Risk weight not found', 404);
            }
            return $this->respondWithSuccess($riskWeight, 'Risk weight returned successfully');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }

    //Get all risk weights
    public function getAllRiskWeights(Request $request)
    {
        try {
            //Find Weights
            $riskWeights = $this->riskRepository->getAllWeights();
            return $this->respondWithSuccess($riskWeights, 'Risk weights returned successfully');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }
}
