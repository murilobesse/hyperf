# ⚙️ Configurações

Guia de configuração do Hyperf e seus componentes.

---

## 📋 Visão Geral

As configurações do Hyperf estão organizadas em `config/`:

```
config/
├── config.php              # Configurações gerais
├── routes.php              # Rotas (se não usar Attributes)
└── autoload/
    ├── annotations.php     # Scan de annotations/attributes
    ├── server.php          # Servidor HTTP
    ├── middlewares.php     # Middlewares globais
    ├── databases.php       # Banco de dados
    ├── redis.php           # Redis
    ├── logger.php          # Logs
    ├── exceptions.php      # Handlers de exceção
    ├── listeners.php       # Listeners de eventos
    ├── commands.php        # Comandos CLI
    ├── processes.php       # Processos customizados
    ├── aspects.php         # AOP
    ├── dependencies.php    # Injeção de dependência
    ├── cache.php           # Cache
    └── devtool.php         # Ferramentas de dev
```

---

## 📄 Arquivos Principais

### config/config.php

Configurações gerais da aplicação:

```php
<?php

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;

return [
    'app_name' => env('APP_NAME', 'skeleton'),
    'app_env' => env('APP_ENV', 'dev'),
    'scan_cacheable' => env('SCAN_CACHEABLE', false),
    StdoutLoggerInterface::class => [
        'log_level' => [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::DEBUG,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ],
    ],
];
```

---

### config/autoload/annotations.php

Configuração do scan de attributes:

```php
<?php

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
        ],
        'ignore_annotations' => [
            'mixin',
        ],
    ],
];
```

---

### config/autoload/server.php

Configuração do servidor HTTP:

```php
<?php

return [
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                SwooleEvent::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
    ],
];
```

---

### config/autoload/middlewares.php

Middlewares globais por servidor:

```php
<?php

return [
    'http' => [
        \App\Middleware\LogMiddleware::class,
    ],
];
```

---

### config/autoload/databases.php

Configuração de banco de dados:

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
    ],
];
```

---

### config/autoload/redis.php

Configuração do Redis:

```php
<?php

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];
```

---

### config/autoload/logger.php

Configuração de logs:

```php
<?php

return [
    'default' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                'maxFiles' => 7,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
];
```

---

### config/autoload/exceptions.php

Handlers de exceção:

```php
<?php

return [
    'handler' => [
        'http' => [
            Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler::class,
            App\Exception\Handler\AppExceptionHandler::class,
        ],
    ],
];
```

---

### config/autoload/listeners.php

Listeners de eventos:

```php
<?php

return [
    Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class,
];
```

---

## 🔑 Variáveis de Ambiente (.env)

### Configurações Principais

```env
# Aplicação
APP_NAME=skeleton
APP_ENV=dev

# Scan Cache (false para dev, true para prod)
SCAN_CACHEABLE=false

# Banco de Dados
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hyperf
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
DB_PREFIX=

# Redis
REDIS_HOST=localhost
REDIS_AUTH=(null)
REDIS_PORT=6379
REDIS_DB=0
```

---

## 📊 Tabela de Configurações

| Arquivo | Descrição | Uso |
|---------|-----------|-----|
| `config.php` | Configurações gerais | App name, env, log |
| `annotations.php` | Scan de attributes | Paths, ignore |
| `server.php` | Servidor HTTP | Host, port, type |
| `middlewares.php` | Middlewares globais | Por servidor |
| `databases.php` | Banco de dados | Conexões, pool |
| `redis.php` | Redis | Conexão, pool |
| `logger.php` | Logs | Handler, formatter |
| `exceptions.php` | Handlers de exceção | Por contexto |
| `listeners.php` | Listeners de eventos | Eventos do framework |

---

## 🔄 Ambiente Dev vs Prod

### Desenvolvimento

```env
APP_ENV=dev
SCAN_CACHEABLE=false
```

- Logs detalhados
- Cache desabilitado
- Hot reload ativo

### Produção

```env
APP_ENV=prod
SCAN_CACHEABLE=true
```

- Logs otimizados
- Cache habilitado
- Máxima performance

---

## ✅ Boas Práticas

1. **Use `env()` para valores sensíveis:**
   ```php
   'password' => env('DB_PASSWORD', ''),
   ```

2. **Mantenha `.env.example` atualizado:**
   ```env
   DB_PASSWORD=  # Deixe vazio no exemplo
   ```

3. **Nunca commit `.env`:**
   - Adicione ao `.gitignore`

4. **Use valores padrão seguros:**
   ```php
   'debug' => env('APP_DEBUG', false),
   ```

5. **Organize por componente:**
   - Um arquivo por componente no `autoload/`

---

## 🔗 Links Relacionados

- [Estrutura do Projeto](estrutura.md)
- [Docker e Docker Compose](docker.md)
- [Rotas e Controllers](rotas-controllers.md)

---

## 📖 Links Úteis

- [Hyperf Configuration Documentation](https://hyperf.wiki/3.1/#/en/config)
- [Hyperf Environment Variables](https://hyperf.wiki/3.1/#/en/config?id=env)
