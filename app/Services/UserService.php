<?php

namespace App\Services;

use App\Repositories\UserRepository;


class UserService
{
    private $UserRepository;

    public function __construct(UserRepository $UserRepository)
    {
        $this->UserRepository = $UserRepository;
    }

    public function createUser(array $data)
    {
        $data['password'] = bcrypt($data['password']);

        return $this->UserRepository->create($data);
    }
}
