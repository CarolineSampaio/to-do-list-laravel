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

    public function listTasks(array $filters, $userId)
    {
        return $this->taskRepository->list($filters, $userId);
    }

    public function updateTask($id, $userId, array $data)
    {
        $task = $this->taskRepository->findOne($id, $userId);
        if (!$task) return false;

        $task = $this->taskRepository->update($task, $data);
        return $task;
    }

    public function deleteTask($userId, $id)
    {
        $task = $this->taskRepository->findOne($id, $userId);
        if (!$task) return false;

        $this->taskRepository->delete($task);
        return true;
    }

    public function completeTask($id, $userId)
    {
        $task = $this->taskRepository->findOne($id, $userId);
        if (!$task) return false;

        $isCompleted = !$task->is_completed;
        $this->taskRepository->updateTaskCompletionStatus($task, $isCompleted, $userId);

        $task->load('completedBy');
        return $task;
    }
}
