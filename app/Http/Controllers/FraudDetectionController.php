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

    //1 Pass the neccessary paremeters into the service
    //2 Sum the result
    //3. Return to the client the neccessary risk evualtion
}
