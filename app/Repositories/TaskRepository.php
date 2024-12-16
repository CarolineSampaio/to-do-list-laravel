<?php

namespace App\Repositories;

use App\Interfaces\TaskRepositoryInterface;
use App\Models\Task;

class TaskRepository implements TaskRepositoryInterface
{
    public function create(array $data)
    {
        return Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
        ]);
    }

    public function attachUserToTask($task, $userId): void
    {
        $task->users()->attach($userId);
    }

    public function list(array $filters, $userId)
    {
        $tasks = Task::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });

        if (isset($filters['category'])) {
            $tasks->where('category_id', $filters['category']);
        }

        if (isset($filters['completed'])) {
            $tasks->where('is_completed', filter_var($filters['completed'], FILTER_VALIDATE_BOOLEAN));
        }

        return $tasks->get();
    }
}
