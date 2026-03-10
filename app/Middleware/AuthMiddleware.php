<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware de Autenticação
 * Verifica se o usuário está logado (via token)
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Pega o token do header Authorization
        $token = $request->getHeaderLine('Authorization');

        // Verifica se o token existe
        if (empty($token)) {
            return $this->unauthorized('Token não informado');
        }

        // Remove o prefixo "Bearer " se existir
        $token = str_replace('Bearer ', '', $token);

        // Valida o token (aqui você faria a validação real)
        if (!$this->validateToken($token)) {
            return $this->unauthorized('Token inválido');
        }

        // Token válido - continua para o próximo middleware/controller
        return $handler->handle($request);
    }

    private function validateToken(string $token): bool
    {
        // Simulação de validação
        // Na prática, valide com JWT, sessão, banco de dados, etc.
        return $token === 'token-secreto-123';
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $data = [
            'success' => false,
            'message' => $message,
        ];
        
        $response = new \GuzzleHttp\Psr7\Response();
        $response->getBody()->write(json_encode($data));
        
        return $response->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
}
