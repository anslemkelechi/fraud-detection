<?php

namespace App\Repository;

use App\Models\User;
use App\Contracts\UserInterface;

class UserRepository implements UserInterface
{

    public function createUser(array $data)
    {
        $user = User::create($data);
        return $user;
    }

    public function getAllUsers()
    {
        $users = User::all();
        return $users;
    }

    public function updateUser($id, array $data)
    {
        $user = User::where("id", $id)->first();
        $user->name = $data['name'] || $user->name;
        $user->email = $data['email'] || $user->email;
        $user->shipping_address = $data['shipping_address'] || $user->shipping_address;
        $user->security_question = $data['security_question'] || $user->security_question;
        $user->save();

        return $user;
    }


    public function getSingleUser($id)
    {
        $user = User::where("id", $id)->first();
        return $user;
    }
}
