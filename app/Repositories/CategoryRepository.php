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

    public function show($user_id, $id)
    {
        return Category::where('user_id', $user_id)
            ->where('id', $id)
            ->get();
    }

    public function update($category, $data)
    {
        $category->update($data);
        return $category;
    }

    public function delete($category)
    {
        $category->delete();
        return true;
    }
}
