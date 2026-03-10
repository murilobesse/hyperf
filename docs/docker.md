# 🐳 Docker e Docker Compose

Guia completo de configuração e uso do Docker para desenvolvimento com Hyperf.

---

## 📋 Visão Geral

Este projeto utiliza Docker para criar um ambiente de desenvolvimento consistente e isolado, eliminando a necessidade de instalar PHP, Swoole e outras dependências diretamente no sistema.

### Arquitetura

| Arquivo | Descrição |
|---------|-----------|
| `dev.Dockerfile` | Imagem baseada em `hyperf/hyperf:8.4-alpine-v3.21-swoole` |
| `docker-compose.yml` | Orquestração do container com volumes e portas |
| `.dockerignore` | Arquivos ignorados durante o build |

---

## 🚀 Quick Start

### 1. Subir o container

```bash
# Build e start
docker-compose up -d

# Ou com rebuild forçado
docker-compose up --build -d
```

### 2. Acessar a aplicação

```
http://localhost:9501
```

### 3. Comandos úteis

```bash
# Ver logs em tempo real
docker-compose logs -f hyperf-skeleton

# Ver apenas as últimas linhas
docker-compose logs --tail=50 hyperf-skeleton

# Parar o container
docker-compose down

# Reiniciar o container
docker-compose restart hyperf-skeleton

# Rebuild completo (remove e recria)
docker-compose down && docker-compose up --build -d
```

---

## 📦 Configuração do Container

### docker-compose.yml

```yaml
services:
  hyperf-skeleton:
    container_name: hyperf-skeleton
    image: hyperf-skeleton
    build:
      dockerfile: dev.Dockerfile
      args:
        UID: 1000    # User ID para permissões
        GID: 1000    # Group ID para permissões
    volumes:
      - ./:/opt/www  # Mount do código local no container
    ports:
      - 9501:9501    # Porta do servidor HTTP
    environment:
      - APP_ENV=dev
      - SCAN_CACHEABLE=false
    entrypoint: php
    command: /opt/www/bin/hyperf.php server:watch
    restart: unless-stopped
    # Limites de memória e CPU
    mem_limit: 2g
    memswap_limit: 4g
    cpus: 2.0
```

### dev.Dockerfile

```dockerfile
FROM hyperf/hyperf:8.4-alpine-v3.21-swoole

# Configurações de ambiente
ENV TIMEZONE=America/Sao_Paulo
ENV APP_ENV=dev
ENV SCAN_CACHEABLE=false

# Cria usuário para evitar problemas de permissão
RUN addgroup -g 1000 application && \
    adduser -S -D -u 1000 -G application -s /bin/ash -h /home/application application

# Configurações do PHP
RUN { \
    echo "upload_max_filesize=128M"; \
    echo "post_max_size=128M"; \
    echo "memory_limit=1G"; \
} | tee conf.d/99_overrides.ini

USER application
WORKDIR /opt/www

# Instala dependências
COPY . /opt/www
RUN composer install --no-scripts

EXPOSE 9501

ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "start"]
```

---

## 🛠️ Comandos dentro do Container

```bash
# Acessar o shell do container
docker exec -it hyperf-skeleton bash

# Instalar dependências do Composer
docker exec hyperf-skeleton composer install

# Instalar nova dependência
docker exec hyperf-skeleton composer require vendor/package

# Rodar testes
docker exec hyperf-skeleton php bin/phpunit

# Limpar cache do container
docker exec hyperf-skeleton rm -rf /opt/www/runtime/container

# Ver processos rodando
docker exec hyperf-skeleton ps aux

# Ver extensões PHP carregadas
docker exec hyperf-skeleton php -m

# Ver versão do PHP
docker exec hyperf-skeleton php -v
```

---

## 🔄 Fluxo de Desenvolvimento

### Hot Reload com Watcher

O projeto está configurado para usar o `hyperf/watcher` com hot reload automático.

**Como funciona:**

1. Suba o container com `docker-compose up -d`
2. Edite seus arquivos localmente
3. O watcher detecta as mudanças e reinicia o servidor automaticamente

**Arquivos monitorados:**
- `app/**/*.php`
- `config/**/*.php`
- `.env`

**Veja nos logs:**
```
[INFO] File changed: app/Controller/UserController.php
[INFO] Restarting server...
```

---

## ⚙️ Variáveis de Ambiente

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| `APP_ENV` | `dev` | Ambiente (dev, test, prod) |
| `SCAN_CACHEABLE` | `false` | Cache de annotations (true em prod) |
| `TIMEZONE` | `Asia/Shanghai` | Fuso horário do container |
| `UID` | `1000` | ID do usuário no container |
| `GID` | `1000` | ID do grupo no container |

---

## ⚠️ Solução de Problemas

| Problema | Solução |
|----------|---------|
| Container não inicia | Verifique logs: `docker-compose logs hyperf-skeleton` |
| Porta 9501 em uso | Altere a porta: `"9502:9501"` |
| Erro de permissão | `docker exec hyperf-skeleton chown -R application:application /opt/www` |
| Composer falha | `docker exec hyperf-skeleton composer install --ignore-platform-reqs` |
| Container cai imediatamente | Verifique erros de sintaxe PHP nos logs |
| Erro de git ownership | `docker exec hyperf-skeleton git config --global --add safe.directory /opt/www` |

---

## 🔗 Links Relacionados

- [Estrutura do Projeto](estrutura.md)
- [Hot Reload (Watcher)](watcher.md)
- [Solução de Problemas](troubleshooting.md)

---

## 📖 Links Úteis

- [Documentação oficial do Hyperf](https://hyperf.wiki)
- [Imagens Docker do Hyperf](https://hub.docker.com/r/hyperf/hyperf)
- [Repositório Docker oficial](https://github.com/hyperf/hyperf-docker)
- [Docker Compose Reference](https://docs.docker.com/compose/)
