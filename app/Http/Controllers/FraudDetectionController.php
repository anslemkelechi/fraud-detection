<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\RiskInterface;
use App\Contracts\BlacklistInterface;
use App\Contracts\UserInterface;
use App\Contracts\TransactionInterface;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Services\GeolocationService;
use App\Services\RiskCalulationService;
use Illuminate\Support\Facades\Log;

class FraudDetectionController extends Controller
{
    protected $riskRepository;
    protected $userRepository;
    protected $blacklistRepository;
    protected $transactionRepository;


    public function __construct(RiskInterface $riskRepository, UserInterface $userRepository, BlacklistInterface $blacklistRepository, TransactionInterface $transactionRepository)
    {
        $this->riskRepository = $riskRepository;
        $this->userRepository = $userRepository;
        $this->blacklistRepository = $blacklistRepository;
        $this->transactionRepository = $transactionRepository;
    }


    //Evaluate Risk Value;

    public function evaluateTransaction(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'userId' => 'required|integer',
                'amount' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return $this->respondWithError($validator->errors()->first(), 422);
            }

            //Get Public IP
            $geoService = new GeolocationService();
            $locationIp = $geoService->getPublicIp();

            //Set Geolocation
            $location = $geoService->getLocationFromIp($locationIp);

            $transaction = [
                "user_id" => $request->userId,
                "amount" => $request->amount,
                "ip_address" => $locationIp,
                "device_fingerprint" => request()->header('User-Agent')
            ];
            $riskCalulationService = new RiskCalulationService(
                $this->transactionRepository,
                $this->userRepository,
                $this->riskRepository,
                $this->blacklistRepository
            );
            $result = $riskCalulationService->evaluateTransaction($transaction);

            //We want to create a new transaction;
            $transactionData = [
                "user_id" => $transaction['user_id'],
                "amount" => $transaction['amount'],
                "ip_address" => $transaction['ip_address'],
                "device_fingerprint" => $transaction['device_fingerprint'],
                "risk_score" => $result['risk_score'],
                "recommendation" => $result['recommendation'],
                "is_new_device" => $result['is_new_device']
            ];

            $createdTransaction = $this->transactionRepository->createTransaction($transactionData);

            //Create New Transaction Ip Transaction is Approved
            if ($result['risk_score'] >= 0 && $result['risk_score'] <= 39) {

                // Check if the transaction IP already exists
                $existingTransactionIp = $this->transactionRepository->getAllTransactionIps($transaction['user_id'], $transaction['ip_address']);
                if (!$existingTransactionIp) {

                    $ipData = [
                        "ip_address" => $createdTransaction->ip_address,
                        "transaction_id" => $createdTransaction->id,
                        "user_id" => $createdTransaction->user_id
                    ];

                    $createdIp = $this->transactionRepository->createTransactionIp($ipData);
                }
            };

            //Create New Blacklisted Ip if Transaction is declined
            if ($result['risk_score'] >= 60 && $result['risk_score'] <= 100) {

                // Check if the transaction IP already exists
                $existingTransactionIp = $this->blacklistRepository->getAllBlacklistedIps($transaction['user_id'], $transaction['ip_address']);
                if (!$existingTransactionIp) {

                    $ipData = [
                        "ip_address" => $createdTransaction->ip_address,
                        "transaction_id" => $createdTransaction->id,
                        "user_id" => $createdTransaction->user_id,
                        "reason" => implode(', ', $result['possible_reasons'])
                    ];

                    $createdIp = $this->blacklistRepository->createBlacklistIp($ipData);
                }
            };

            return $this->respondWithSuccess($result, 'Transaction evaluated successfully');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }
}
