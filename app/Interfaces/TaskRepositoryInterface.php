<?php

namespace App\Interfaces;

interface TaskRepositoryInterface
{
    public function create(array $data);
    public function attachUserToTask($task, $userId): void;
    public function list(array $filters, $userId);
    public function findOne($id, $userId);
    public function update($task, array $data);
    public function delete($task): void;
    public function updateTaskCompletionStatus($task, $isCompleted, $userId = null);
}
