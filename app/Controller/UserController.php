<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\{
    Controller,
    GetMapping,
    PostMapping,
    PutMapping,
    DeleteMapping,
    PatchMapping,
    Middleware,
    Middlewares
};

/**
 * Controller de exemplo com múltiplos endpoints usando Attributes
 * 
 * Todas as rotas são definidas diretamente no controller
 */
#[Controller]
#[Middleware(\App\Middleware\LogMiddleware::class)]
class UserController extends AbstractController
{
    /**
     * Lista todos os usuários
     * GET /users
     * Rota pública (apenas log)
     */
    #[GetMapping(path: '/users')]
    #[Middleware(\App\Middleware\AuthMiddleware::class)]
    public function index(): array
    {
        return [
            'success' => true,
            'message' => 'Lista de usuários',
            'data' => [
                ['id' => 1, 'name' => 'João'],
                ['id' => 2, 'name' => 'Maria'],
                ['id' => 3, 'name' => 'Pedro'],
            ],
        ];
    }

    /**
     * Cria um novo usuário
     * POST /users
     * Requer autenticação
     */
    #[PostMapping(path: '/users')]
    #[Middleware(\App\Middleware\AuthMiddleware::class)]
    public function store(): array
    {
        return [
            'success' => true,
            'message' => 'Usuário criado com sucesso',
            'data' => ['id' => 4, 'name' => 'Novo Usuário'],
        ];
    }

    /**
     * Retorna um usuário específico
     * GET /users/{id}
     * Rota pública (apenas log)
     */
    #[GetMapping(path: '/users/{id}')]
    public function show(int $id): array
    {
        return [
            'success' => true,
            'message' => "Dados do usuário {$id}",
            'data' => ['id' => $id, 'name' => "Usuário {$id}"],
        ];
    }

    /**
     * Atualiza um usuário
     * PUT /users/{id}
     * Requer autenticação
     */
    #[PutMapping(path: '/users/{id}')]
    #[Middleware(\App\Middleware\AuthMiddleware::class)]
    public function update(int $id): array
    {
        return [
            'success' => true,
            'message' => "Usuário {$id} atualizado",
            'data' => ['id' => $id, 'name' => "Usuário {$id} Atualizado"],
        ];
    }

    /**
     * Remove um usuário
     * DELETE /users/{id}
     * Requer autenticação + ser admin
     */
    #[DeleteMapping(path: '/users/{id}')]
    #[Middleware(\App\Middleware\AuthMiddleware::class)]
    #[Middleware(\App\Middleware\AdminMiddleware::class)]
    public function destroy(int $id): array
    {
        return [
            'success' => true,
            'message' => "Usuário {$id} removido",
        ];
    }
}
