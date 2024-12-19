<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user1 = User::create([
            'name' => 'Usuário teste 1',
            'email' => 'teste@example.com',
            'password' => bcrypt('Password123@'),
        ]);

        $user2 = User::create([
            'name' => 'Usuário teste 2',
            'email' => 'teste2@example.com',
            'password' => bcrypt('Password123@'),
        ]);

        $category1 = Category::create([
            'title' => 'Urgente',
            'user_id' => $user1->id,
        ]);

        $category2 = Category::create([
            'title' => 'Importante',
            'user_id' => $user1->id,
        ]);

        $task1 = Task::create([
            'title' => 'Finalizar a documentação do projeto',
            'description' => 'Escrever a documentação técnica para entrega final.',
            'category_id' => $category1->id,
        ]);
        $user1->tasks()->attach($task1->id);
        $user2->tasks()->attach($task1->id);

        $task2 = Task::create([
            'title' => 'Revisar código do módulo de autenticação',
            'description' => 'Garantir que todas as validações e fluxos estejam corretos.',
            'category_id' => $category1->id,
            'is_completed' => true,
            'completed_at' => now(),
            'completed_by' => $user2->id,
        ]);
        $user1->tasks()->attach($task2->id);
        $user2->tasks()->attach($task2->id);

        $task3 = Task::create([
            'title' => 'Planejar o backlog do próximo sprint',
            'description' => 'Definir prioridades e alinhar com o time.',
            'category_id' => $category2->id,
        ]);
        $user1->tasks()->attach($task3->id);
        $user2->tasks()->attach($task3->id);

        $task4 = Task::create([
            'title' => 'Testar a integração com o sistema de pagamentos',
            'description' => 'Realizar testes manuais e automatizados.',
            'category_id' => $category2->id,
            'is_completed' => true,
            'completed_at' => now(),
            'completed_by' => $user1->id,
        ]);
        $user1->tasks()->attach($task4->id);
        $user2->tasks()->attach($task4->id);

        $task5 = Task::create([
            'title' => 'Estudar novas ferramentas para DevOps',
            'description' => 'Pesquisar e avaliar ferramentas que possam otimizar o fluxo de trabalho.',
        ]);
        $user1->tasks()->attach($task5->id);
        $user2->tasks()->attach($task5->id);

        $task6 = Task::create([
            'title' => 'Configurar o ambiente de desenvolvimento local',
            'description' => 'Instalar dependências e configurar o Docker.',
            'is_completed' => true,
            'completed_at' => now(),
            'completed_by' => $user1->id,
        ]);
        $user1->tasks()->attach($task6->id);
        $user2->tasks()->attach($task6->id);
    }
}
