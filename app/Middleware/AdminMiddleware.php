<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware de Admin
 * Verifica se o usuário é administrador
 */
class AdminMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Pega informações do usuário (que foram adicionadas pelo AuthMiddleware)
        $userRole = $request->getHeaderLine('X-User-Role');

        if ($userRole !== 'admin') {
            return $this->forbidden('Acesso permitido apenas para administradores');
        }

        return $handler->handle($request);
    }

    private function forbidden(string $message): ResponseInterface
    {
        $data = [
            'success' => false,
            'message' => $message,
        ];
        
        $response = new \GuzzleHttp\Psr7\Response();
        $response->getBody()->write(json_encode($data));
        
        return $response->withStatus(403)
            ->withHeader('Content-Type', 'application/json');
    }
}
