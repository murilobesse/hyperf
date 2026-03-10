<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware de Log
 * Registra todas as requisições recebidas
 */
class LogMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // ANTES - Registra a requisição
        $method = $request->getMethod();
        $uri = $request->getUri();
        $time = date('Y-m-d H:i:s');

        error_log("[{$time}] {$method} {$uri}");

        // Processa a requisição (passa para o próximo middleware/controller)
        $response = $handler->handle($request);

        // DEPOIS - Registra a resposta
        $status = $response->getStatusCode();
        error_log("[{$time}] {$method} {$uri} - Status: {$status}");

        return $response;
    }
}
