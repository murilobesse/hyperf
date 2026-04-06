# 🛣️ Rotas e Controllers

Guia de definição de rotas e criação de controllers usando Attributes PHP 8.

---

## 📋 Visão Geral

Hyperf suporta duas formas de definir rotas:

1. **Attributes PHP 8** (Recomendado ⭐) - Define rotas diretamente no controller
2. **Arquivo de rotas** - Define rotas em `config/routes.php`

---

## 🎯 Attributes PHP 8 (Recomendado)

### Vantagens

- ✅ Rotas próximas ao código do controller
- ✅ Melhor organização e legibilidade
- ✅ Auto-completar na IDE
- ✅ Refatoração mais fácil

### Imports necessários

Use **Group Use** para reduzir linhas:

```php
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
```

---

## 📦 Attributes Disponíveis

### #[Controller]

Define a classe como controller:

```php
#[Controller(prefix: '/api/v1')]
class UserController extends AbstractController
{
    // ...
}
```

| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| `prefix` | string | `''` | Prefixo comum para todas as rotas |
| `server` | string | `'http'` | Servidor (http, https, ws) |
| `options` | array | `[]` | Opções adicionais |

---

### #[GetMapping]

Rota para método GET:

```php
#[GetMapping(path: '/users')]
public function index(): array
{
    // GET /users
}
```

---

### #[PostMapping]

Rota para método POST:

```php
#[PostMapping(path: '/users')]
public function store(): array
{
    // POST /users
}
```

---

### #[PutMapping]

Rota para método PUT:

```php
#[PutMapping(path: '/users/{id}')]
public function update(int $id): array
{
    // PUT /users/{id}
}
```

---

### #[DeleteMapping]

Rota para método DELETE:

```php
#[DeleteMapping(path: '/users/{id}')]
public function destroy(int $id): array
{
    // DELETE /users/{id}
}
```

---

### #[PatchMapping]

Rota para método PATCH:

```php
#[PatchMapping(path: '/users/{id}')]
public function patch(int $id): array
{
    // PATCH /users/{id}
}
```

---

## 📝 Exemplos Completos

### Controller Básico

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\{
    Controller,
    GetMapping
};

#[Controller]
class IndexController extends AbstractController
{
    #[GetMapping(path: '/')]
    public function index(): array
    {
        return [
            'success' => true,
            'message' => 'Bem-vindo à API!',
        ];
    }
}
```

### CRUD Completo

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\{
    Controller,
    GetMapping,
    PostMapping,
    PutMapping,
    DeleteMapping,
    Middleware
};

#[Controller(prefix: '/api/users')]
class UserController extends AbstractController
{
    #[GetMapping(path: '')]
    public function index(): array
    {
        // GET /api/users
        return ['users' => []];
    }

    #[GetMapping(path: '/{id}')]
    public function show(int $id): array
    {
        // GET /api/users/1
        return ['user' => ['id' => $id]];
    }

    #[PostMapping(path: '')]
    #[Middleware(AuthMiddleware::class)]
    public function store(): array
    {
        // POST /api/users (com autenticação)
        return ['created' => true];
    }

    #[PutMapping(path: '/{id}')]
    #[Middleware(AuthMiddleware::class)]
    public function update(int $id): array
    {
        // PUT /api/users/1 (com autenticação)
        return ['updated' => true];
    }

    #[DeleteMapping(path: '/{id}')]
    #[Middleware(AuthMiddleware::class)]
    #[Middleware(AdminMiddleware::class)]
    public function destroy(int $id): array
    {
        // DELETE /api/users/1 (auth + admin)
        return ['deleted' => true];
    }
}
```

---

## 🔗 Parâmetros de Rota

### Parâmetro Obrigatório

```php
#[GetMapping(path: '/users/{id}')]
public function show(int $id): array
{
    return ['id' => $id];
}
```

### Parâmetro Opcional

```php
#[GetMapping(path: '/users/{name?}')]
public function byName(string $name = null): array
{
    return ['name' => $name ?? 'all'];
}
```

### Parâmetro com Regex

```php
#[GetMapping(path: '/users/{id:\d+}')]
public function show(int $id): array
{
    // Apenas números
    return ['id' => $id];
}

#[GetMapping(path: '/posts/{slug:[a-z]+}')]
public function show(string $slug): array
{
    // Apenas letras minúsculas
    return ['slug' => $slug];
}
```

---

## 🔐 Middlewares em Rotas

### Middleware Único

```php
#[GetMapping(path: '/users')]
#[Middleware(AuthMiddleware::class)]
public function index(): array
{
    // ...
}
```

### Múltiplos Middlewares

```php
#[DeleteMapping(path: '/users/{id}')]
#[Middleware(AuthMiddleware::class)]
#[Middleware(AdminMiddleware::class)]
public function destroy(int $id): array
{
    // ...
}
```

### Middleware no Controller (todas as rotas)

```php
#[Controller(prefix: '/admin')]
#[Middleware(AuthMiddleware::class)]
class AdminController extends AbstractController
{
    #[GetMapping(path: '/dashboard')]
    public function dashboard(): array
    {
        // Todas as rotas requerem autenticação
    }
}
```

---

## 📁 Arquivo de Rotas (Alternativo)

Se preferir não usar Attributes, use `config/routes.php`:

```php
<?php

use Hyperf\HttpServer\Router\Router;

// Rota simples
Router::get('/', 'App\Controller\IndexController@index');

// Rota com múltiplos métodos
Router::addRoute(['GET', 'POST'], '/users', 'App\Controller\UserController@store');

// Grupo de rotas
Router::addGroup('/api', function () {
    Router::get('/users', 'App\Controller\UserController@index');
    Router::post('/users', 'App\Controller\UserController@store');
});
```

---

## 🆚 Comparação: Attributes vs Arquivo

| Recurso | Attributes | Arquivo routes.php |
|---------|------------|-------------------|
| Organização | No controller | Centralizado |
| Auto-completar | ✅ Sim | ⚠️ Parcial |
| Refatoração | ✅ Automática | ❌ Manual |
| Legibilidade | ✅ Alta | ⚠️ Média |
| Recomendado | ✅ Sim | Para casos específicos |

---

## 📊 Tabela de Métodos HTTP

| Attribute | Método HTTP | Uso Comum |
|-----------|-------------|-----------|
| `#[GetMapping]` | GET | Listar/Buscar recursos |
| `#[PostMapping]` | POST | Criar recurso |
| `#[PutMapping]` | PUT | Atualizar recurso completo |
| `#[PatchMapping]` | PATCH | Atualização parcial |
| `#[DeleteMapping]` | DELETE | Remover recurso |

---

## ✅ Boas Práticas

1. **Use prefixos no controller:**
   ```php
   #[Controller(prefix: '/api/v1/users')]
   ```

2. **Nomeie métodos claramente:**
   ```php
   public function index()      // Listar
   public function show()       // Mostrar um
   public function store()      // Criar
   public function update()     // Atualizar
   public function destroy()    // Remover
   ```

3. **Use middlewares no nível correto:**
   - Controller: para todas as rotas
   - Método: para rotas específicas

4. **Valide parâmetros com regex:**
   ```php
   #[GetMapping(path: '/users/{id:\d+}')]
   ```

---

## 🔗 Links Relacionados

- [Estrutura do Projeto](estrutura.md)
- [Middlewares](middlewares.md)
- [Configurações](configuracoes.md)

---

## 📖 Links Úteis

- [Hyperf Router Documentation](https://hyperf.wiki/3.1/#/en/router)
- [PHP 8 Attributes](https://www.php.net/manual/en/language.attributes.overview.php)
