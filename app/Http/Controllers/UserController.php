<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\UserInterface;
use App\Contracts\TransactionInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Services\GeolocationService;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $userRepository;
    protected $transactionRepository;

    public function __construct(UserInterface $userRepository, TransactionInterface $transactionRepository)
    {
        $this->userRepository = $userRepository;
        $this->transactionRepository = $transactionRepository;
    }


    //Create new User 
    public function createUser(Request $request)
    {
        DB::beginTransaction();
        try {
            $name = $request->name;
            $email = $request->email;
            $shippingAddress = $request->shippingAddress;
            $deviceFingerPrint = request()->header('User-Agent');
            $securityQuestion = $request->securityQuestion;

            //Add Validations
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|string',
                'shippingAddress' => 'string',
                'securityQuestion' => 'array',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return $this->respondWithError($validator->errors()->first(), 422);
            }

            //Get Public IP
            $geoService = new GeolocationService();
            $locationIp = $geoService->getPublicIp();

            //Set Geolocation
            $location = $geoService->getLocationFromIp($locationIp);

            $data = [
                "name" => $name,
                "email" => $email,
                "shipping_address" => $shippingAddress,
                "device_fingerprint" => $deviceFingerPrint,
                "location_ip" => $locationIp,
                "security_question" => $securityQuestion,
                "location" => $location
            ];

            $user = $this->userRepository->createUser($data);

            //Lets create some sample transactions for user 
            $transactionData = [
                [
                    "user_id" => $user->id,
                    "amount" => 1500,
                    "ip_address" => $locationIp,
                    "device_fingerprint" => $deviceFingerPrint,
                    "is_new_device" => false,
                    "risk_score" => 10,
                    "recommendation" => "Approve"
                ],
                [
                    "user_id" => $user->id,
                    "amount" => 2000,
                    "ip_address" => $locationIp,
                    "device_fingerprint" => $deviceFingerPrint,
                    "is_new_device" => false,
                    "risk_score" => 20,
                    "recommendation" => "Approve"
                ]

            ];

            foreach ($transactionData as $data) {
                $transaction = $this->transactionRepository->createTransaction($data);
                //Lets Create Some Transactions Ips for user
                $transactionIps = [
                    "ip_address" => $locationIp,
                    "transaction_id" => $transaction->id,
                    "user_id" => $user->id
                ];
                $transactionIp = $this->transactionRepository->createTransactionIp($transactionIps);
            }
            DB::commit();

            return $this->respondWithSuccess($user, 'User created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError($e->getMessage(), 500);
        }
    }

    //Update User
    public function updateUser(Request $request)
    {
        try {
            $userId = $request->route("userId");
            $name = $request->name;
            $email = $request->email;
            $shippingAddress = $request->shippingAddress;
            $securityQuestion = $request->securityQuestion;

            //Add Validations
            $validator = Validator::make($request->all(), [
                'name' => 'string',
                'email' => 'integer',
                'shippingAddress' => 'string',
                'securityQuestion' => 'array',
                'userId' => 'integer'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return $this->respondWithError($validator->errors()->first(), 422);
            }

            $data = [
                "name" => $name,
                "email" => $email,
                "shipping_address" => $shippingAddress,
                "security_question" => $securityQuestion,
            ];

            //Find user 
            $existingUser = $this->userRepository->getSingleUser($userId);
            if (empty($existingUser)) {
                return $this->respondWithError('User not found', 404);
            }

            $user = $this->userRepository->updateUser($userId, $data);
            return $this->respondWithSuccess($user, 'User updated successfully');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }

    //Get All Users
    public function getAllUsers(Request $request)
    {
        try {
            //Find users
            $users = $this->userRepository->getAllUsers();
            return $this->respondWithSuccess($users, 'Users returned successfully');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }
}
