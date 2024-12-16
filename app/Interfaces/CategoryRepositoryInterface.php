<?php

namespace App\Interfaces;

interface CategoryRepositoryInterface
{
    public function create(array $data);
    public function listAll($user_id);
    public function show($user_id, $id);
    public function update($id, $user_id, $data);
}
