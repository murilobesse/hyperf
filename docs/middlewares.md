# 🔐 Middlewares

Guia de criação e uso de middlewares no Hyperf.

---

## 📋 O que é Middleware?

Middleware é uma camada que intercepta a requisição HTTP **antes** de chegar ao controller e a resposta **depois** de sair do controller.

### Fluxo em Camadas (Cebola) 🧅

```
Requisição
    ↓
┌─────────────────────────┐
│  Middleware 1 (ANTES)   │
└─────────────────────────┘
    ↓
┌─────────────────────────┐
│  Middleware 2 (ANTES)   │
└─────────────────────────┘
    ↓
┌─────────────────────────┐
│     Controller          │
└─────────────────────────┘
    ↓
┌─────────────────────────┐
│  Middleware 2 (DEPOIS)  │
└─────────────────────────┘
    ↓
┌─────────────────────────┐
│  Middleware 1 (DEPOIS)  │
└─────────────────────────┘
    ↓
Resposta
```

---

## 🎯 Casos de Uso Comuns

| Middleware | Função |
|------------|--------|
| **Auth** | Verificar se usuário está logado |
| **CORS** | Permitir requisições de outros domínios |
| **Rate Limit** | Limitar número de requisições |
| **Log** | Registrar todas as requisições |
| **Sanitização** | Limpar dados de entrada |
| **Admin** | Verificar permissão de administrador |

---

## 🛠️ Criando um Middleware

### Estrutura Básica

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExemploMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // ANTES - Processa a requisição
        
        // Passa para o próximo middleware/controller
        $response = $handler->handle($request);
        
        // DEPOIS - Processa a resposta
        
        return $response;
    }
}
```

---

## 📝 Exemplos Práticos

### 1. Middleware de Autenticação

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Pega o token do header
        $token = $request->getHeaderLine('Authorization');

        // Verifica se o token existe
        if (empty($token)) {
            return $this->unauthorized('Token não informado');
        }

        // Remove prefixo "Bearer "
        $token = str_replace('Bearer ', '', $token);

        // Valida o token
        if (!$this->validateToken($token)) {
            return $this->unauthorized('Token inválido');
        }

        // Token válido - continua
        return $handler->handle($request);
    }

    private function validateToken(string $token): bool
    {
        // Valide com JWT, sessão, banco, etc.
        return $token === 'token-secreto-123';
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $body = new \Hyperf\HttpMessage\Stream\SwooleStream();
        $body->write(json_encode([
            'success' => false,
            'message' => $message,
        ]));
        
        $response = new \GuzzleHttp\Psr7\Response();
        return $response->withStatus(401)
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json');
    }
}
```

---

### 2. Middleware de Log

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // ANTES - Registra a requisição
        $method = $request->getMethod();
        $uri = $request->getUri();
        $time = date('Y-m-d H:i:s');

        error_log("[{$time}] {$method} {$uri}");

        // Processa a requisição
        $response = $handler->handle($request);

        // DEPOIS - Registra a resposta
        $status = $response->getStatusCode();
        error_log("[{$time}] {$method} {$uri} - Status: {$status}");

        return $response;
    }
}
```

---

### 3. Middleware de Admin

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Verifica papel do usuário
        $userRole = $request->getHeaderLine('X-User-Role');

        if ($userRole !== 'admin') {
            return $this->forbidden('Acesso apenas para administradores');
        }

        return $handler->handle($request);
    }

    private function forbidden(string $message): ResponseInterface
    {
        $body = new \Hyperf\HttpMessage\Stream\SwooleStream();
        $body->write(json_encode([
            'success' => false,
            'message' => $message,
        ]));
        
        $response = new \GuzzleHttp\Psr7\Response();
        return $response->withStatus(403)
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json');
    }
}
```

---

## 📦 Usando Middlewares

### No Controller (Attribute)

```php
use Hyperf\HttpServer\Annotation\{
    Controller,
    GetMapping,
    Middleware
};

#[Controller]
class UserController extends AbstractController
{
    #[GetMapping(path: '/users')]
    public function index(): array
    {
        // Rota pública
        return ['users' => []];
    }

    #[PostMapping(path: '/users')]
    #[Middleware(AuthMiddleware::class)]
    public function store(): array
    {
        // Requer autenticação
        return ['created' => true];
    }

    #[DeleteMapping(path: '/users/{id}')]
    #[Middleware(AuthMiddleware::class)]
    #[Middleware(AdminMiddleware::class)]
    public function destroy(int $id): array
    {
        // Requer autenticação + admin
        return ['deleted' => true];
    }
}
```

### Middleware no Controller (todas as rotas)

```php
#[Controller]
#[Middleware(LogMiddleware::class)]
class UserController extends AbstractController
{
    // Todas as rotas serão logadas
}
```

### Middleware Global

Em `config/autoload/middlewares.php`:

```php
return [
    'http' => [
        \App\Middleware\LogMiddleware::class,
    ],
];
```

---

## 🔄 Ordem de Execução

Quando múltiplos middlewares são aplicados:

```php
#[Middleware(A::class)]
#[Middleware(B::class)]
#[Middleware(C::class)]
public function index(): array
```

**Ordem de execução:**

```
Requisição → A → B → C → Controller → C → B → A → Resposta
```

---

## 📊 Tabela de Status HTTP

| Status | Código | Uso |
|--------|--------|-----|
| `200 OK` | 200 | Sucesso |
| `401 Unauthorized` | 401 | Não autenticado |
| `403 Forbidden` | 403 | Sem permissão |
| `404 Not Found` | 404 | Recurso não encontrado |
| `500 Internal Server Error` | 500 | Erro do servidor |

---

## ✅ Boas Práticas

1. **Mantenha middlewares focados:**
   - Um middleware = uma responsabilidade

2. **Use nomes descritivos:**
   - `AuthMiddleware` em vez de `CheckLoginMiddleware`

3. **Retorne sempre a resposta:**
   ```php
   return $handler->handle($request);
   ```

4. **Trate erros graciosamente:**
   ```php
   if (!$isValid) {
       return $this->errorResponse('Erro');
   }
   ```

5. **Documente o middleware:**
   ```php
   /**
    * Middleware de autenticação via token JWT
    */
   class AuthMiddleware implements MiddlewareInterface
   ```

---

## 🔗 Links Relacionados

- [Rotas e Controllers](rotas-controllers.md)
- [Configurações](configuracoes.md)
- [Solução de Problemas](troubleshooting.md)

---

## 📖 Links Úteis

- [Hyperf Middleware Documentation](https://hyperf.wiki/3.1/en/middleware/middleware)
- [PSR-15 Middleware](https://www.php-fig.org/psr/psr-15/)
