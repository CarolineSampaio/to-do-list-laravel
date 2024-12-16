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
}
