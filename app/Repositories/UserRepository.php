<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{

    public function create(array $data)
    {
        return User::create($data);
    }

    public function findUserByEmail(array $data)
    {
        return User::where('email', $data['email'])->first();
    }
}
