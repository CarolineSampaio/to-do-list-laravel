<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
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

    public function login(LoginUserRequest $request)
    {
        $validated = $request->safe()->only(['email', 'password']);

        $token = $this->userService->login($validated);
        if (!$token) return $this->error('Email ou senha incorretos', Response::HTTP_UNAUTHORIZED, ['error' => 'Credenciais inválidas.']);

        return $this->response('Usuário logado com sucesso.', Response::HTTP_OK, ['token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response('', Response::HTTP_NO_CONTENT, []);
    }
}
