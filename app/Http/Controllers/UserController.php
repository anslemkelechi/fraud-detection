<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\UserInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Services\GeolocationService;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    //Create new User 
    public function createUser(Request $request)
    {
        try {
            $name = $request->name;
            $email = $request->email;
            $shippingAddress = $request->shippingAddress;
            $deviceFingerPrint = request()->header('User-Agent');
            $locationIp = request()->ip();
            $securityQuestion = $request->securityQuestion;

            //Add Validations
            $validator = Validator::make($request->all(), [
                'name' => 'string',
                'email' => 'integer',
                'shippingAddress' => 'string',
                'securityQuestion' => 'array',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return $this->respondWithError($validator->errors()->first(), 422);
            }

            //Set Geolocation
            $geoService = new GeolocationService();
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
            return $this->respondWithSuccess($user, 'User created successfully');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }

    //Update User
    public function updateUser(Request $request)
    {
        try {
            $userId = $request->userId;
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
