<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Contracts\TransactionInterface;
use App\Contracts\UserInterface;
use App\Contracts\RiskInterface;
use App\Contracts\BlacklistInterface;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;




class RiskCalulationService
{
    protected $transactionRepository;
    protected $userRepository;
    protected $riskWeightRepository;

    protected $blacklistRepository;


    public function __construct(
        TransactionInterface $transactionRepository,
        UserInterface $userRepository,
        RiskInterface $riskWeightRepository,
        BlacklistInterface $blacklistRepository
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->userRepository = $userRepository;
        $this->riskWeightRepository = $riskWeightRepository;
        $this->blacklistRepository = $blacklistRepository;
    }

    /**
     * Evaluate a transaction and calculate risk score
     */
    public function evaluateTransaction($transaction)
    {

        $riskScore = 0;

        // Check transaction amount
        $riskScore += $this->evaluateAmountRisk($transaction['amount']);

        // Check IP address
        $ipAddressScore = $this->evaluateIpRisk($transaction['user_id'], $transaction['ip_address']);
        $riskScore += $ipAddressScore['score'];
        $possibleReasons = $ipAddressScore['reason'];

        // Check transaction frequency
        $riskScore += $this->evaluateFrequencyRisk($transaction['user_id']);

        // Check device fingerprint
        $deviceScore = $this->evaluateDeviceChangeRisk($transaction['user_id'], $transaction['device_fingerprint']);
        $riskScore += $deviceScore;

        // Aggregate risk and return recommendation
        return $this->generateRecommendation($riskScore, $possibleReasons, $deviceScore);
    }

    private function evaluateAmountRisk($amount)
    {
        // Base risk percentage for the amount (20% of the total risk)
        $riskPercentage = $this->riskWeightRepository->getWeightByIdentifier('transaction_amount');
        $baseRiskPercentage = $riskPercentage->weight / 100;  //This means it would cover for 20% ot total rosk score.

        // Get the integer part of the amount (before the decimal point)
        $wholePart = floor($amount);

        // Calculate the order of magnitude (in terms of thousands, ten thousands, etc.)
        $magnitude = floor(log10($wholePart));

        // Ensure that the magnitude starts from 0 for 1000 and increases from there
        $magnitude = max($magnitude - 3, 0);  // Subtract 3 to start from thousands, ensuring 1000 = 1 (thousand)

        // Calculate the risk increment for each order of magnitude
        $riskIncrement = $magnitude * 0.5;  // For each order, we add 0.5 to the risk

        // The total risk score for the amount is the risk increment capped at 1 (100%)
        $amountRisk = min($riskIncrement, 1) * $baseRiskPercentage;

        return $amountRisk;
    }


    private function evaluateIpRisk($userId, $providedIp)
    {
        if (!$providedIp) {
            return ['score' => 0, 'reason' => 'No IP provided'];
        }
        $ipRiskWeight = $this->riskWeightRepository->getWeightByIdentifier('ip_address');
        $ipRiskWeight = $ipRiskWeight->weight;

        //total IP risk score
        $totalIpRiskScore = 0;
        $reason = [];

        // Scenario 1: Check if the provided IP is blacklisted
        $isBlacklisted = $this->blacklistRepository->getSingleBlacklistedIp($providedIp);

        if ($isBlacklisted) {
            // If the IP is blacklisted, the full IP risk weight is allocated
            $totalIpRiskScore = $ipRiskWeight;
            $reason[] = 'Blacklisted IP';
        } else {
            // Scenario 2: If the provided IP doesn't match the registered user IP, add 20% of the IP risk weight
            $user = $this->userRepository->getSingleUser($userId);

            if ($user->location_ip !== $providedIp) {
                $totalIpRiskScore += $ipRiskWeight * 0.2;
                $reason[] = 'IP mismatch with registered user IP';
            }

            // Scenario 3: If the IP is not in the transaction_ips list, add 40% of the IP risk weight
            $ipExistsInTransactions = $this->transactionRepository->getAllTransactionIps($userId, $providedIp);
            if (!$ipExistsInTransactions) {
                $totalIpRiskScore += $ipRiskWeight * 0.4;
                $reason[] = 'IP not found in transaction history';
            }
        }

        //Capped at the IP risk weight
        if ($totalIpRiskScore > $ipRiskWeight) {
            $totalIpRiskScore = $ipRiskWeight;
        }

        return ['score' => $totalIpRiskScore, 'reason' => implode(', ', $reason)];
    }

    private function evaluateFrequencyRisk($userId)
    {
        $frequencyRiskWeight = $this->riskWeightRepository->getWeightByIdentifier('transaction_frequency');
        $frequencyRiskWeight = $frequencyRiskWeight->weight;

        // Get the current time and the time 10 minutes ago

        $currentTime = Carbon::now();
        $timeWindowStart = Carbon::now()->subMinutes(10);

        // Get the count of transactions within the last 10 minutes
        $transactionCount = $this->transactionRepository->getTransactionWithinTime($userId, $timeWindowStart, $currentTime);

        // Initialize the total frequency risk score
        $totalFrequencyRiskScore = 0;

        if ($transactionCount == 0) {
            return $totalFrequencyRiskScore;
        }
        if ($transactionCount >= 3) {
            // 1. Calculate the risk score based on the transaction count
            // 2. The first transaction in the last 10 minutes adds 30%, 
            // 3. and each additional transaction adds 10% more of the risk weight
            $additionalTransactions = $transactionCount - 3;

            // Add the 30% for the 3rd transaction
            $totalFrequencyRiskScore = $frequencyRiskWeight * 0.3;

            // Add 10% for each additional transaction
            $totalFrequencyRiskScore += $frequencyRiskWeight * 0.1 * $additionalTransactions;
        }

        // Capped at the frequency risk weight
        if ($totalFrequencyRiskScore > $frequencyRiskWeight) {
            $totalFrequencyRiskScore = $frequencyRiskWeight;
        }

        return $totalFrequencyRiskScore;
    }


    private function evaluateDeviceChangeRisk($userId, $providedDeviceFingerprint)
    {
        // Get the device fingerprint risk weight
        $deviceFingerprintRiskWeight = $this->riskWeightRepository->getWeightByIdentifier('device_fingerprint');
        $deviceFingerprintRiskWeight = $deviceFingerprintRiskWeight->weight;

        // Get the device fingerprint from the user object
        $user = $this->userRepository->getSingleUser($userId);

        // Initialize total device change risk score
        $totalDeviceChangeRiskScore = 0;
        if ($user->device_fingerprint === null) {
            return 0;
        }
        // Check if the provided device fingerprint matches the user's device fingerprint
        if ($user->device_fingerprint !== $providedDeviceFingerprint) {
            // If the device is new, assign the full risk weight
            $totalDeviceChangeRiskScore = $deviceFingerprintRiskWeight;
        }

        return $totalDeviceChangeRiskScore;
    }
    private function generateRecommendation($riskScore, $possibleReasons, $deviceScore)
    {
        // Ensure possible reasons is an array
        if (!is_array($possibleReasons)) {
            $possibleReasons = explode(',', $possibleReasons); 
        }
    
        // Default recommendation
        $recommendation = 'Approve';
    
        // If the IP is blacklisted, always decline
        if (in_array('Blacklisted IP', $possibleReasons)) {
            $recommendation = 'Decline';
        } 
        // Otherwise, base recommendation on risk score
        elseif ($riskScore > 60) {
            $recommendation = 'Decline';
        } 
        elseif ($riskScore > 40) {
            $recommendation = 'Flag';
        }
    
        return [
            'risk_score' => $riskScore,
            'recommendation' => $recommendation,
            'possible_reasons' => $possibleReasons,
            'is_new_device' => $deviceScore > 1
        ];
    }
}
