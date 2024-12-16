<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $request->merge(['user_id' => Auth::id()]);
            return $next($request);
        });
    }

    public function store(CategoryRequest $request, CategoryService $categoryService)
    {
        $data = $request->safe()->only(['title']);

        $data['title'] = strtolower($data['title']);
        $data['user_id'] = $request->user_id;

        $category = $categoryService->createCategory($data);

        return $this->response('', Response::HTTP_CREATED, $category);
    }
}
