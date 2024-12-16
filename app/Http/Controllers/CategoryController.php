<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;

        $this->middleware(function ($request, $next) {
            $request->merge(['user_id' => Auth::id()]);
            return $next($request);
        });
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->safe()->only(['title']);

        $data['title'] = strtolower($data['title']);
        $data['user_id'] = $request->user_id;

        $category = $this->categoryService->createCategory($data);

        return $this->response('Categoria criada com sucesso', Response::HTTP_CREATED, $category);
    }

    public function index(Request $request)
    {
        $categories = $this->categoryService->ListAllCategories($request->user_id);

        return $this->response('Categorias do usuário listadas com sucesso', Response::HTTP_OK, $categories->toArray());
    }

    public function show(Request $request, $id)
    {
        $category = $this->categoryService->showCategory($request->user_id, $id);

        if ($category->isEmpty()) return $this->error('Categoria não encontrada', Response::HTTP_NOT_FOUND);

        return $this->response('Categoria do usuário listada com sucesso', Response::HTTP_OK, $category->toArray());
    }

    public function update($id, CategoryRequest $request)
    {
        $data = $request->safe()->only(['title']);

        $data['title'] = strtolower($data['title']);
        $data['user_id'] = $request->user_id;

        $category = $this->categoryService->updateCategory($id, $request->user_id, $data);
        if (!$category) return $this->error('Categoria não encontrada', Response::HTTP_NOT_FOUND);

        return $this->response('Categoria atualizada com sucesso', Response::HTTP_OK, $category->toArray());
    }
}
