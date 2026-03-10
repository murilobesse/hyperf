# ❌ Solução de Problemas

Guia de troubleshooting para problemas comuns no Hyperf.

---

## 🔍 Diagnóstico Inicial

### 1. Verificar status do container

```bash
docker-compose ps
```

**Saída esperada:**
```
NAME              STATUS          PORTS
hyperf-skeleton   Up 2 minutes    0.0.0.0:9501->9501/tcp
```

### 2. Verificar logs

```bash
docker-compose logs --tail=50 hyperf-skeleton
```

### 3. Testar conexão

```bash
curl http://localhost:9501/
```

---

## 🚨 Problemas Comuns

### Container não inicia

**Sintoma:**
```
Container hyperf-skeleton exited with code 1
```

**Soluções:**

1. **Verifique os logs:**
   ```bash
   docker-compose logs hyperf-skeleton
   ```

2. **Verifique erros de sintaxe PHP:**
   ```bash
   docker exec hyperf-skeleton php -l app/Controller/IndexController.php
   ```

3. **Verifique se há erros de dependências:**
   ```bash
   docker exec hyperf-skeleton composer install --ignore-platform-reqs
   ```

---

### Porta 9501 já em uso

**Sintoma:**
```
Address already in use
```

**Solução:**

Altere a porta no `docker-compose.yml`:

```yaml
ports:
  - "9502:9501"  # Use 9502 externa, 9501 interna
```

Ou pare o processo usando a porta:

```bash
# Windows
netstat -ano | findstr :9501
taskkill /PID <PID> /F

# Linux/Mac
lsof -ti:9501 | xargs kill -9
```

---

### Erro de permissão de arquivos

**Sintoma:**
```
Permission denied: /opt/www/runtime/container
```

**Solução:**

```bash
docker exec hyperf-skeleton chown -R application:application /opt/www
docker-compose restart hyperf-skeleton
```

---

### Composer falha ao instalar

**Sintoma:**
```
Composer install failed
Memory exhausted
```

**Solução:**

1. **Aumente a memória do container:**
   ```yaml
   mem_limit: 2g
   ```

2. **Instale sem scripts:**
   ```bash
   docker exec hyperf-skeleton composer install --no-scripts
   ```

3. **Ignore requisitos de plataforma:**
   ```bash
   docker exec hyperf-skeleton composer install --ignore-platform-reqs
   ```

---

### Watcher não detecta mudanças

**Sintoma:**
- Arquivos mudam mas servidor não reinicia

**Soluções:**

1. **Verifique se `.watcher.php` existe:**
   ```bash
   docker exec hyperf-skeleton ls -la /opt/www/.watcher.php
   ```

2. **Confirme o comando no docker-compose.yml:**
   ```yaml
   command: /opt/www/bin/hyperf.php server:watch
   ```

3. **Aumente o intervalo de scan:**
   ```php
   // .watcher.php
   'scan_interval' => 5000, // 5 segundos
   ```

4. **Verifique as permissões:**
   ```bash
   docker exec hyperf-skeleton ls -la /opt/www/app/
   ```

---

### Erro de git ownership

**Sintoma:**
```
fatal: detected dubious ownership in repository at '/opt/www'
```

**Solução:**

```bash
docker exec hyperf-skeleton git config --global --add safe.directory /opt/www
docker-compose restart hyperf-skeleton
```

---

### Container cai em loop (reinicia constantemente)

**Sintoma:**
- Container reinicia a cada poucos segundos

**Soluções:**

1. **Verifique logs em tempo real:**
   ```bash
   docker-compose logs -f hyperf-skeleton
   ```

2. **Desabilite o restart automático temporariamente:**
   ```yaml
   restart: "no"
   ```

3. **Verifique erros de inicialização:**
   ```bash
   docker exec hyperf-skeleton php -v
   docker exec hyperf-skeleton php -m
   ```

---

### Erro "Class not found"

**Sintoma:**
```
Error: Class "App\Controller\UserController" not found
```

**Soluções:**

1. **Limpe o cache:**
   ```bash
   docker exec hyperf-skeleton rm -rf /opt/www/runtime/container
   docker-compose restart hyperf-skeleton
   ```

2. **Regenerate autoload:**
   ```bash
   docker exec hyperf-skeleton composer dump-autoload
   ```

3. **Verifique o namespace no arquivo:**
   ```php
   namespace App\Controller;  // Deve bater com o path
   ```

---

### Rotas não funcionam (404)

**Sintoma:**
```
Not Found - 404
```

**Soluções:**

1. **Verifique se o controller tem #[Controller]:**
   ```php
   #[Controller]  // Necessário!
   class UserController extends AbstractController
   ```

2. **Verifique o path do attribute:**
   ```php
   #[GetMapping(path: '/users')]  // Path correto?
   ```

3. **Limpe o cache de rotas:**
   ```bash
   docker exec hyperf-skeleton rm -rf /opt/www/runtime/container
   docker-compose restart hyperf-skeleton
   ```

---

### Middleware não executa

**Sintoma:**
- Middleware não intercepta requisição

**Soluções:**

1. **Verifique se o middleware está importado:**
   ```php
   use App\Middleware\AuthMiddleware;
   
   #[Middleware(AuthMiddleware::class)]  // Import correto?
   ```

2. **Verifique se implementa a interface:**
   ```php
   class AuthMiddleware implements MiddlewareInterface
   ```

3. **Verifique a ordem dos attributes:**
   ```php
   #[Middleware(A::class)]
   #[Middleware(B::class)]  // Ordem importa!
   ```

---

### Erro de banco de dados

**Sintoma:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Soluções:**

1. **Verifique configurações no `.env`:**
   ```env
   DB_HOST=localhost  # Ou nome do serviço Docker
   DB_PORT=3306
   DB_DATABASE=hyperf
   DB_USERNAME=root
   DB_PASSWORD=
   ```

2. **Verifique se o banco está acessível:**
   ```bash
   docker exec hyperf-skeleton ping -c 4 localhost
   ```

3. **Teste conexão:**
   ```bash
   docker exec hyperf-skeleton php -r "new PDO('mysql:host=localhost;dbname=hyperf', 'root', '');"
   ```

---

### Erro de memória excedida

**Sintoma:**
```
Fatal error: Allowed memory size exhausted
```

**Soluções:**

1. **Aumente memória no docker-compose.yml:**
   ```yaml
   mem_limit: 2g  # ou mais
   ```

2. **Aumente memory_limit do PHP:**
   ```dockerfile
   RUN echo "memory_limit=2G" >> /etc/php84/conf.d/99_overrides.ini
   ```

3. **Otimize o código:**
   - Evite carregar muitos dados de uma vez
   - Use paginação

---

## 🛠️ Comandos de Debug

### Verificar status do container

```bash
docker-compose ps
```

### Ver logs em tempo real

```bash
docker-compose logs -f hyperf-skeleton
```

### Acessar o container

```bash
docker exec -it hyperf-skeleton bash
```

### Ver processos rodando

```bash
docker exec hyperf-skeleton ps aux
```

### Ver extensões PHP carregadas

```bash
docker exec hyperf-skeleton php -m
```

### Ver versão do PHP

```bash
docker exec hyperf-skeleton php -v
```

### Verificar sintaxe PHP

```bash
docker exec hyperf-skeleton php -l app/Controller/IndexController.php
```

### Limpar cache

```bash
docker exec hyperf-skeleton rm -rf /opt/www/runtime/container
```

### Reiniciar container

```bash
docker-compose restart hyperf-skeleton
```

### Rebuild completo

```bash
docker-compose down && docker-compose up --build -d
```

---

## 📊 Tabela de Códigos de Erro

| Código | Significado | Ação |
|--------|-------------|------|
| `1` | Erro geral | Verifique logs |
| `137` | Memory limit | Aumente memória |
| `139` | Segmentation fault | Verifique extensão Swoole |
| `255` | Erro fatal PHP | Verifique sintaxe |

---

## 📞 Ainda com problemas?

1. **Verifique a documentação oficial:**
   - https://hyperf.wiki

2. **Procure issues no GitHub:**
   - https://github.com/hyperf/hyperf/issues

3. **Compartilhe informações:**
   - Logs completos
   - Versão do Docker
   - Versão do Hyperf
   - Passos para reproduzir

---

## 🔗 Links Relacionados

- [Docker e Docker Compose](docker.md)
- [Configurações](configuracoes.md)
- [Hot Reload (Watcher)](watcher.md)

---

## 📖 Links Úteis

- [Hyperf Documentation](https://hyperf.wiki)
- [Hyperf GitHub Issues](https://github.com/hyperf/hyperf/issues)
- [Docker Troubleshooting](https://docs.docker.com/config/containers/troubleshoot/)
