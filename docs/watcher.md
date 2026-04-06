# 🔥 Hot Reload com Watcher

Guia de configuração e uso do hot reload automático com `hyperf/watcher`.

---

## 📋 Visão Geral

O `hyperf/watcher` é um pacote que monitora mudanças nos arquivos do projeto e reinicia o servidor automaticamente, proporcionando uma experiência de desenvolvimento muito mais ágil.

---

## ⚙️ Configuração

### 1. Arquivo `.watcher.php`

Localizado na raiz do projeto:

```php
<?php

declare(strict_types=1);
use Hyperf\Watcher\Driver\ScanFileDriver;

return [
    'driver' => ScanFileDriver::class,
    'bin' => PHP_BINARY,
    'watch' => [
        'dir' => ['app', 'config'],
        'file' => ['.env'],
        'scan_interval' => 2000, // 2 segundos
    ],
    'ext' => ['.php', '.env'],
];
```

### 2. docker-compose.yml

O comando deve ser `server:watch`:

```yaml
services:
  hyperf-skeleton:
    # ... resto da config ...
    command: /opt/www/bin/hyperf.php server:watch
```

---

## 🚀 Como Usar

### 1. Subir o container

```bash
docker-compose up -d
```

### 2. Editar arquivos

Faça alterações em:
- `app/Controller/*.php`
- `app/Middleware/*.php`
- `config/*.php`
- `.env`

### 3. O watcher detecta automaticamente

Nos logs você verá:

```
[INFO] File changed: app/Controller/UserController.php
[INFO] Restarting server...
[INFO] HTTP Server listening at 0.0.0.0:9501
```

---

## 📊 Configurações do Watcher

### `driver`

Driver usado para monitorar arquivos:

- `ScanFileDriver::class` - Usa scan por arquivo (funciona em todos os sistemas)
- `FswatchDriver::class` - Usa fswatch (requer instalação adicional)

### `watch.dir`

Diretórios monitorados:

```php
'dir' => ['app', 'config'],
```

### `watch.file`

Arquivos específicos monitorados:

```php
'file' => ['.env'],
```

### `scan_interval`

Intervalo de verificação em milissegundos:

```php
'scan_interval' => 2000, // 2 segundos
```

### `ext`

Extensões de arquivo monitoradas:

```php
'ext' => ['.php', '.env'],
```

---

## ✅ O que é recarregado automaticamente

| Tipo de Arquivo | Hot Reload |
|-----------------|------------|
| Controllers | ✅ Sim |
| Middlewares | ✅ Sim |
| Models | ✅ Sim |
| Configurações | ✅ Sim |
| Rotas (Attributes) | ✅ Sim |
| .env | ✅ Sim |

---

## ⚠️ Quando precisa de restart manual

| Situação | Ação Necessária |
|----------|-----------------|
| Criar nova classe | Restart automático |
| Instalar pacote Composer | `docker-compose restart` |
| Mudar Dockerfile | `docker-compose up --build` |
| Mudar docker-compose.yml | `docker-compose up -d` |

---

## 🔍 Debug do Watcher

### Verificar se está funcionando

```bash
# Ver logs em tempo real
docker-compose logs -f hyperf-skeleton
```

### Se o watcher não detectar mudanças

1. **Verifique se `.watcher.php` existe:**
   ```bash
   docker exec hyperf-skeleton ls -la /opt/www/.watcher.php
   ```

2. **Confirme o comando no docker-compose.yml:**
   ```yaml
   command: /opt/www/bin/hyperf.php server:watch
   ```

3. **Verifique as permissões:**
   ```bash
   docker exec hyperf-skeleton ls -la /opt/www/app/
   ```

4. **Aumente o intervalo de scan:**
   ```php
   'scan_interval' => 5000, // 5 segundos
   ```

---

## 🆚 Comparação: Watcher vs Normal

| Recurso | Com Watcher | Modo Normal |
|---------|-------------|-------------|
| Reinício automático | ✅ Sim | ❌ Não |
| Precisa de restart manual | ❌ Não | ✅ Sim |
| Performance | Leve overhead | Máxima |
| Recomendado para | Desenvolvimento | Produção |

---

## 📝 Exemplo de Uso

### Cenário: Criando novo endpoint

1. **Crie o controller:**

```php
#[Controller]
class ProductController extends AbstractController
{
    #[GetMapping(path: '/products')]
    public function index(): array
    {
        return ['products' => []];
    }
}
```

2. **Salve o arquivo**

3. **O watcher detecta e reinicia:**

```
[INFO] File changed: app/Controller/ProductController.php
[INFO] Restarting server...
```

4. **Teste imediatamente:**

```bash
curl http://localhost:9501/products
```

---

## ⚡ Dicas de Performance

1. **Monitore apenas o necessário:**
   ```php
   'dir' => ['app', 'config'], // Não inclua vendor/
   ```

2. **Ajuste o intervalo:**
   - Desenvolvimento ativo: `1000` (1s)
   - Desenvolvimento normal: `2000` (2s)
   - Máquina lenta: `5000` (5s)

3. **Limite o cache:**
   ```env
   SCAN_CACHEABLE=false  # Sempre false em dev com watcher
   ```

---

## 🔗 Links Relacionados

- [Docker e Docker Compose](docker.md)
- [Rotas e Controllers](rotas-controllers.md)
- [Solução de Problemas](troubleshooting.md)

---

## 📖 Links Úteis

- [Hyperf Watcher Documentation](https://hyperf.wiki/3.1/#/en/watcher)
- [GitHub - hyperf/watcher](https://github.com/hyperf/hyperf/tree/master/src/watcher)
