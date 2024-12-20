<?php

namespace App\Contracts;

interface UserInterface
{

    public function createUser(array $data);

    public function updateUser($id, array $data);

    public function getAllUsers();

    public function getSingleUser($id);
}
