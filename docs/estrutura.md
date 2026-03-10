# 🏗️ Estrutura do Projeto

Visão geral da organização de arquivos e diretórios do projeto Hyperf.

---

## 📁 Árvore de Diretórios

```
hyperf/
├── app/
│   ├── Controller/
│   │   ├── AbstractController.php    # Base para todos os controllers
│   │   ├── IndexController.php       # Controller de exemplo (GET /)
│   │   └── UserController.php        # CRUD de usuários com Attributes
│   ├── Middleware/
│   │   ├── AuthMiddleware.php        # Valida token de autenticação
│   │   ├── AdminMiddleware.php       # Verifica permissão de administrador
│   │   └── LogMiddleware.php         # Registra requisições e respostas
│   ├── Exception/Handler/
│   │   └── AppExceptionHandler.php   # Handler de exceções da aplicação
│   ├── Listener/
│   │   └── DBQueryExecutedListener.php
│   └── Model/
│       └── Model.php                 # Base para models do banco de dados
│
├── bin/
│   └── hyperf.php                    # Script de inicialização do Hyperf
│
├── config/
│   ├── config.php                    # Configurações gerais da aplicação
│   ├── routes.php                    # Rotas (vazio ao usar Attributes)
│   └── autoload/
│       ├── annotations.php           # Config de scan de annotations/attributes
│       ├── server.php                # Config do servidor HTTP (porta 9501)
│       ├── middlewares.php           # Middlewares globais por servidor
│       ├── databases.php             # Config de conexões de banco de dados
│       ├── redis.php                 # Config de conexões Redis
│       ├── logger.php                # Config de logs
│       ├── exceptions.php            # Config de handlers de exceção
│       ├── listeners.php             # Config de listeners de eventos
│       ├── commands.php              # Config de comandos CLI
│       ├── processes.php             # Config de processos customizados
│       ├── aspects.php               # Config de AOP (Aspect Oriented Programming)
│       ├── dependencies.php          # Config de injeção de dependência
│       ├── cache.php                 # Config de cache
│       └── devtool.php               # Config de ferramentas de desenvolvimento
│
├── runtime/
│   ├── container/                    # Cache de container DI
│   ├── logs/
│   │   └── hyperf.log                # Logs da aplicação
│   └── hyperf.pid                    # PID do servidor
│
├── test/
│   ├── Cases/
│   │   └── ExampleTest.php
│   └── Bootstrap.php
│
├── .env                              # Variáveis de ambiente
├── .env.example                      # Exemplo de variáveis de ambiente
├── .watcher.php                      # Configuração do Hot Reload Watcher
├── docker-compose.yml                # Configuração Docker Compose
├── dev.Dockerfile                    # Dockerfile para desenvolvimento
├── composer.json                     # Dependências do projeto
├── composer.lock                     # Lock das dependências
└── README.md                         # Este arquivo
```

---

## 📂 Principais Diretórios

### `app/`

Contém todo o código da aplicação:

| Subdiretório | Descrição |
|--------------|-----------|
| `Controller/` | Controllers HTTP que recebem as requisições |
| `Middleware/` | Middlewares para interceptar requisições |
| `Model/` | Models de banco de dados |
| `Exception/Handler/` | Handlers para tratamento de exceções |
| `Listener/` | Listeners para eventos do framework |

### `config/`

Arquivos de configuração da aplicação:

| Arquivo | Descrição |
|---------|-----------|
| `config.php` | Configurações gerais (app_name, app_env, etc.) |
| `routes.php` | Definição de rotas (quando não usa Attributes) |
| `autoload/` | Configurações específicas por componente |

### `runtime/`

Arquivos temporários gerados em tempo de execução:

| Subdiretório/Arquivo | Descrição |
|----------------------|-----------|
| `container/` | Cache de dependências e annotations |
| `logs/` | Logs da aplicação |
| `hyperf.pid` | PID do processo do servidor |

### `test/`

Arquivos de teste:

| Arquivo | Descrição |
|---------|-----------|
| `Cases/` | Casos de teste PHPUnit |
| `Bootstrap.php` | Bootstrap dos testes |

---

## 📄 Arquivos Principais

### `.env`

Variáveis de ambiente da aplicação:

```env
APP_NAME=skeleton
APP_ENV=dev

DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hyperf
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=localhost
REDIS_PORT=6379
```

### `.watcher.php`

Configuração do hot reload:

```php
return [
    'driver' => ScanFileDriver::class,
    'bin' => PHP_BINARY,
    'watch' => [
        'dir' => ['app', 'config'],
        'file' => ['.env'],
        'scan_interval' => 2000,
    ],
    'ext' => ['.php', '.env'],
];
```

### `docker-compose.yml`

Configuração do Docker Compose para desenvolvimento.

### `composer.json`

Dependências do projeto PHP.

---

## 🔗 Links Relacionados

- [Docker e Docker Compose](docker.md)
- [Configurações](configuracoes.md)
- [Rotas e Controllers](rotas-controllers.md)
