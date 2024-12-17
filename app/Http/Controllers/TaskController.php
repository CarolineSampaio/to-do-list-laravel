<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;

        $this->middleware(function ($request, $next) {
            $request->merge(['userId' => Auth::id()]);
            return $next($request);
        });
    }

    public function store(TaskRequest $request)
    {
        $data = $request->safe()->only(['title', 'description', 'category_id']);

        $task = $this->taskService->createTask($data, $request->userId);

        return $this->response('Tarefa criada com sucesso', Response::HTTP_CREATED, $task->toArray());
    }

    public function index(Request $request)
    {
        $filters = $request->only(['category', 'completed']);

        $tasks = $this->taskService->listTasks($filters, $request->userId);

        return $this->response('Tarefas listadas com sucesso', Response::HTTP_OK, $tasks->toArray());
    }

    public function update(TaskRequest $request, $id)
    {
        $data = $request->safe()->only(['title', 'description', 'category_id']);

        $task = $this->taskService->updateTask($id, $request->userId, $data);
        if (!$task) return $this->error('Tarefa não encontrada', Response::HTTP_NOT_FOUND);

        return $this->response('Tarefa atualizada com sucesso', Response::HTTP_OK, $task->toArray());
    }

    public function destroy($id, Request $request)
    {
        $task = $this->taskService->deleteTask($request->userId, $id);
        if (!$task) return $this->error('Tarefa não encontrada', Response::HTTP_NOT_FOUND);

        return $this->response('', Response::HTTP_NO_CONTENT);
    }

    public function toggleComplete(Request $request, $id)
    {
        $task = $this->taskService->completeTask($id, $request->userId);
        if (!$task) return $this->error('Tarefa não encontrada', Response::HTTP_NOT_FOUND);

        return $this->response('Tarefa atualizada com sucesso', Response::HTTP_OK, $task);
    }
}
