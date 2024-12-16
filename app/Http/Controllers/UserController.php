<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->safe()->only(['name', 'email', 'password']);

        $user =  $this->userService->createUser($validated);

        return $this->response('Usuário cadastrado com sucesso.', Response::HTTP_CREATED, $user);
    }
}
