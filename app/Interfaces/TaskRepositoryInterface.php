<?php

namespace App\Interfaces;

interface TaskRepositoryInterface
{
    public function create(array $data);
    public function attachUserToTask($task, $userId): void;
    public function list(array $filters, $userId);
}
