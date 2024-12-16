<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function createCategory($data)
    {
        return $this->categoryRepository->create($data);
    }

    public function listAllCategories($user_id)
    {
        return $this->categoryRepository->listAll($user_id);
    }

    public function showCategory($user_id, $id)
    {
        return $this->categoryRepository->show($user_id, $id);
    }

    public function updateCategory($id, $user_id, $data)
    {
        $category = $this->showCategory($user_id, $id);
        if ($category->isEmpty()) return false;

        return $this->categoryRepository->update($category->first(), $data);
    }

    public function deleteCategory($user_id, $id)
    {
        $category = $this->showCategory($user_id, $id);
        if ($category->isEmpty()) return false;

        return $this->categoryRepository->delete($category->first());
    }
}
