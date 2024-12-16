<?php

namespace App\Repositories;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function listAll($user_id)
    {
        return Category::where('user_id', $user_id)
            ->orderBy('created_At', 'asc')
            ->get();
    }
}
