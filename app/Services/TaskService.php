<?php

namespace App\Services;

use App\Repositories\TaskRepository;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function createTask(array $data, $userId)
    {
        $task = $this->taskRepository->create($data);

        $this->taskRepository->attachUserToTask($task, $userId);

        return $task;
    }
}
