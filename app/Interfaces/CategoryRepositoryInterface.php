<?php

namespace App\Interfaces;

interface CategoryRepositoryInterface
{
    public function create(array $data);
    public function listAll($user_id);
}
