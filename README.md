# Hyperf - Projeto de Estudo

API RESTful desenvolvida com **Hyperf Framework** para fins de estudo e aprendizado.

---

## 🚀 Início Rápido

### Pré-requisitos

- Docker Desktop instalado
- Git (opcional, para versionamento)

### Subir o projeto

```bash
# Build e start do container
docker-compose up -d

# Acessar a API
http://localhost:9501
```

### Testar a API

```bash
# Rota principal
curl http://localhost:9501/

# Listar usuários
curl http://localhost:9501/users

# Criar usuário (com autenticação)
curl -X POST http://localhost:9501/users \
  -H "Authorization: Bearer token-secreto-123"
```

---

## 📚 Documentação Completa

| Tópico | Arquivo |
|--------|---------|
| 🏗️ Estrutura do Projeto | [docs/estrutura.md](docs/estrutura.md) |
| 🐳 Docker e Docker Compose | [docs/docker.md](docs/docker.md) |
| 🔥 Hot Reload (Watcher) | [docs/watcher.md](docs/watcher.md) |
| 🛣️ Rotas e Controllers | [docs/rotas-controllers.md](docs/rotas-controllers.md) |
| 🔐 Middlewares | [docs/middlewares.md](docs/middlewares.md) |
| ⚙️ Configurações | [docs/configuracoes.md](docs/configuracoes.md) |
| ⚡ Paralelismo e Corrotinas | [docs/paralelismo.md](docs/paralelismo.md) |
| ❌ Solução de Problemas | [docs/troubleshooting.md](docs/troubleshooting.md) |

---

## 📁 Estrutura Principal

```
hyperf/
├── app/
│   ├── Controller/       # Controllers HTTP
│   ├── Middleware/       # Middlewares da aplicação
│   └── Model/            # Models de banco de dados
├── config/               # Configurações da aplicação
├── docs/                 # Documentação completa
├── docker-compose.yml    # Configuração Docker
└── .watcher.php          # Configuração do Hot Reload
```

---

## 🎯 Recursos Implementados

- ✅ **REST API** com CRUD completo
- ✅ **Attributes PHP 8** para rotas
- ✅ **Hot Reload** com watcher automático
- ✅ **Middlewares** de autenticação e log
- ✅ **Docker** configurado para desenvolvimento
- ✅ **Paralelismo** com corrotinas (exemplo prático)
- ✅ **Documentação** segmentada por tópico

### 🧪 Exemplo de Paralelismo

O projeto inclui um controller de demonstração de paralelismo:

```bash
# Execução sequencial (bloqueante)
curl http://localhost:9501/parallelism/sequential

# Execução concorrente (não-bloqueante)
curl http://localhost:9501/parallelism/concurrent

# Comparação completa
curl http://localhost:9501/parallelism/compare
```

**Resultado típico:**
- Sequencial: ~340ms (soma de todas as requisições)
- Concorrente: ~150ms (tempo da mais lenta)
- **Ganho: 2-3x mais rápido!** 🚀

Veja mais em [docs/paralelismo.md](docs/paralelismo.md)

---

## 📖 Links Úteis

| Recurso | Link |
|---------|------|
| Documentação Oficial | https://hyperf.wiki |
| Docker Hub | https://hub.docker.com/r/hyperf/hyperf |
| GitHub | https://github.com/hyperf/hyperf |

---

## 📝 Comandos Úteis

```bash
# Ver logs
docker-compose logs -f hyperf-skeleton

# Parar o container
docker-compose down

# Reiniciar
docker-compose restart hyperf-skeleton

# Acessar o container
docker exec -it hyperf-skeleton bash

# Instalar dependência
docker exec hyperf-skeleton composer require vendor/package
```

---

> **Dica:** Consulte a pasta `docs/` para documentação detalhada de cada tópico.
