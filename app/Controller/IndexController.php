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
 * Controller de exemplo para estudo do Hyperf
 * Retorna um JSON estático na rota GET /
 */
#[Controller]
class IndexController extends AbstractController
{
    /**
     * Método principal da rota GET /
     *
     * @return array Retorna um array que será convertido para JSON automaticamente
     */
    #[GetMapping(path: '/')]
    public function index(): array
    {
        return [
            'success' => true,
            'message' => 'Bem-vindo à API Hyperf! teste',
            'data' => [
                'framework' => 'Hyperf',
                'version' => '3.x',
                'php_version' => PHP_VERSION,
                'swoole' => extension_loaded('swoole') ? 'Habilitado' : 'Não disponível',
            ],
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }
}
