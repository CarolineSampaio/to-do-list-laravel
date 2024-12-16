<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

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

    public function login(array $data)
    {
        $user = $this->UserRepository->findUserByEmail($data);

        if (!$user || !Hash::check($data['password'], $user->password)) return false;

        $user->tokens()->delete();
        return $user->createToken('authToken')->plainTextToken;
    }
}
