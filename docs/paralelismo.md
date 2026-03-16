# ⚡ Paralelismo e Corrotinas

Exemplo prático de paralelismo usando corrotinas no Hyperf/Swoole.

---

## 📋 Visão Geral

O Hyperf utiliza **corrotinas** (coroutines) do Swoole para permitir execução concorrente de código, proporcionando ganhos significativos de performance em operações I/O-bound como requisições HTTP, queries de banco de dados, etc.

---

## 🎯 Exemplo Prático: Múltiplas Requisições HTTP

Este projeto inclui um controller de exemplo (`ParallelismController`) que demonstra a diferença entre execução **sequencial** e **concorrente**.

### Rotas Disponíveis

| Rota | Descrição |
|------|-----------|
| `GET /parallelism/sequential` | 3 requisições executadas uma após a outra |
| `GET /parallelism/concurrent` | 3 requisições executadas simultaneamente |
| `GET /parallelism/compare` | Comparação lado a lado das duas abordagens |

---

## 📊 Comparação: Sequencial vs Concorrente

### Sequencial (Bloqueante)

```php
#[GetMapping(path: '/parallelism/sequential')]
public function sequential(): array
{
    $client = $this->clientFactory->create();
    
    foreach ($urls as $url) {
        // Cada requisição bloqueia a próxima
        $response = $client->get($url);
    }
}
```

**Características:**
- ❌ Uma requisição espera a anterior terminar
- ❌ Tempo total = soma de todos os tempos
- ✅ Simples de entender e depurar

**Exemplo:**
```
Requisição 1: 250ms
Requisição 2:  40ms
Requisição 3:  40ms
─────────────────────
Total:         330ms
```

---

### Concorrente (Não-Bloqueante)

```php
#[GetMapping(path: '/parallelism/concurrent')]
public function concurrent(): array
{
    $waitGroup = new WaitGroup();
    
    foreach ($urls as $url) {
        Coroutine::create(function () use ($waitGroup, $url) {
            $waitGroup->add();
            try {
                $client = $this->clientFactory->create();
                $response = $client->get($url);
            } finally {
                $waitGroup->done();
            }
        });
    }
    
    $waitGroup->wait(); // Aguarda todas terminarem
}
```

**Características:**
- ✅ Todas as requisições iniciam simultaneamente
- ✅ Tempo total ≈ tempo da requisição mais lenta
- ⚠️ Requer cuidado com concorrência de recursos

**Exemplo:**
```
Requisição 1: 130ms ─┐
Requisição 2: 127ms ─┼─ Executando em paralelo
Requisição 3: 142ms ─┘
─────────────────────────────
Total:         150ms (62% mais rápido!)
```

---

## 🔍 Resultados Reais

### Exemplo de Resposta - Sequencial

```json
{
  "type": "SEQUENTIAL",
  "description": "Requisições executadas uma após a outra (bloqueante)",
  "results": [
    {
      "request": 1,
      "time_ms": 259.74
    },
    {
      "request": 2,
      "time_ms": 40.36
    },
    {
      "request": 3,
      "time_ms": 40.39
    }
  ],
  "total_time_ms": 340.49,
  "sum_of_requests_ms": 340.49
}
```

### Exemplo de Resposta - Concorrente

```json
{
  "type": "CONCURRENT",
  "description": "Requisições executadas em paralelo com corrotinas (não-bloqueante)",
  "results": [
    {
      "request": 1,
      "time_ms": 124.88
    },
    {
      "request": 2,
      "time_ms": 139.69
    },
    {
      "request": 3,
      "time_ms": 118.76
    }
  ],
  "total_time_ms": 146.17,
  "sum_of_requests_ms": 383.33,
  "time_saved_ms": 237.16,
  "speedup_percentage": "61.87%"
}
```

---

## 📈 Comparação Direta

Use a rota `/parallelism/compare` para ver uma comparação lado a lado:

```bash
curl http://localhost:9501/parallelism/compare
```

**Exemplo de resposta:**

```json
{
  "comparison": {
    "sequential": {
      "total_time_ms": 460.47,
      "requests": [
        {"request": 1, "time_ms": 259.74},
        {"request": 2, "time_ms": 40.36},
        {"request": 3, "time_ms": 40.39}
      ]
    },
    "concurrent": {
      "total_time_ms": 146.17,
      "requests": [
        {"request": 1, "time_ms": 124.88},
        {"request": 2, "time_ms": 139.69},
        {"request": 3, "time_ms": 118.76}
      ]
    },
    "difference_ms": 314.3,
    "speedup": "3.15x mais rápido",
    "time_saved_percentage": "68.26%"
  }
}
```

---

## 🧠 Conceitos Chave

### Corrotinas (Coroutines)

São "threads leves" gerenciadas pelo Swoole (não pelo sistema operacional).

**Vantagens:**
- ✅ Milhares de corrotinas em uma única thread
- ✅ Troca de contexto extremamente rápida
- ✅ Memória mínima (~8KB por corrotina)

### WaitGroup

Sincronizador que aguarda múltiplas corrotinas terminarem:

```php
$waitGroup = new WaitGroup();

foreach ($tasks as $task) {
    Coroutine::create(function () use ($waitGroup, $task) {
        $waitGroup->add();   // Incrementa contador
        try {
            // Executa tarefa
        } finally {
            $waitGroup->done(); // Decrementa contador
        }
    });
}

$waitGroup->wait(); // Bloqueia até contador = 0
```

### Channel

Canal de comunicação entre corrotinas (padrão CSP):

```php
$channel = new Channel(10);

// Producer
Coroutine::create(function () use ($channel) {
    $channel->push($data);
});

// Consumer
Coroutine::create(function () use ($channel) {
    $data = $channel->pop();
});
```

---

## 🎯 Quando Usar Paralelismo

### ✅ Casos Ideais (com exemplos práticos)

#### 1. **Múltiplas requisições HTTP/APIs**

**Cenário:** Você precisa buscar dados de 3 APIs diferentes para montar um dashboard.

```php
// ❌ SEQUENCIAL - Lento (cada um espera o anterior)
$users    = $http->get('https://api.example.com/users');    // 200ms
$products = $http->get('https://api.example.com/products'); // 200ms
$orders   = $http->get('https://api.example.com/orders');   // 200ms
// Tempo total: 600ms

// ✅ CONCORRENTE - Rápido (todos ao mesmo tempo)
Coroutine::create(fn() => $users    = $http->get('https://api.example.com/users'));
Coroutine::create(fn() => $products = $http->get('https://api.example.com/products'));
Coroutine::create(fn() => $orders   = $http->get('https://api.example.com/orders'));
$waitGroup->wait();
// Tempo total: ~200ms (tempo da mais lenta)
```

**Analogia:** 
- **Sequencial:** Fazer 3 compras em lojas diferentes indo e voltando para casa cada vez
- **Concorrente:** Mandar 3 pessoas diferentes, cada uma em uma loja, todos voltam juntos

---

#### 2. **Queries de banco independentes**

**Cenário:** Dashboard que mostra usuários, produtos e estatísticas.

```php
// ❌ SEQUENCIAL
$usersCount    = DB::table('users')->count();    // 50ms
$productsCount = DB::table('products')->count(); // 50ms
$ordersCount   = DB::table('orders')->count();   // 50ms
// Total: 150ms

// ✅ CONCORRENTE
Coroutine::create(fn() => $usersCount    = DB::table('users')->count());
Coroutine::create(fn() => $productsCount = DB::table('products')->count());
Coroutine::create(fn() => $ordersCount   = DB::table('orders')->count());
$waitGroup->wait();
// Total: ~50ms
```

**Importante:** Só use se as queries forem **independentes** (uma não precisa do resultado da outra).

---

#### 3. **Leitura/escrita de arquivos**

**Cenário:** Processar 100 arquivos de log.

```php
// ❌ SEQUENCIAL - Lê um arquivo por vez
foreach ($files as $file) {
    $content = file_get_contents($file); // 10ms cada
    process($content);
}
// 100 arquivos × 10ms = 1000ms (1 segundo)

// ✅ CONCORRENTE - Lê múltiplos arquivos simultaneamente
foreach ($files as $file) {
    Coroutine::create(function () use ($file, $waitGroup) {
        $waitGroup->add();
        try {
            $content = file_get_contents($file);
            process($content);
        } finally {
            $waitGroup->done();
        }
    });
}
$waitGroup->wait();
// ~10-50ms (dependendo do I/O do disco)
```

---

#### 4. **Processamento de filas**

**Cenário:** Enviar 50 emails de notificação.

```php
// ❌ SEQUENCIAL - Um email por vez
foreach ($users as $user) {
    Mail::send($user->email, 'Bem-vindo!'); // 100ms cada
}
// 50 usuários × 100ms = 5000ms (5 segundos!)

// ✅ CONCORRENTE - Múltiplos emails simultaneamente
foreach ($users as $user) {
    Coroutine::create(function () use ($user, $waitGroup) {
        $waitGroup->add();
        try {
            Mail::send($user->email, 'Bem-vindo!');
        } finally {
            $waitGroup->done();
        }
    });
}
$waitGroup->wait();
// ~100-200ms (tempo do email mais lento)
```

---

#### 5. **Operações I/O-bound em geral**

**O que é I/O-bound?** Operações que esperam por:
- Rede (HTTP, API, SOAP)
- Banco de dados
- Arquivos
- Redis/Memcached
- Filas (RabbitMQ, Kafka)

**Regra prática:** Se a operação **espera** por algo externo, é candidata a paralelismo!

---

### ❌ Quando Evitar (com exemplos)

#### 1. **Operações CPU-bound intensivas**

**Cenário:** Processamento de imagem, cálculos complexos.

```php
// ❌ NÃO AJUDA - Corrotinas não aceleram CPU
foreach ($images as $image) {
    Coroutine::create(fn() => processImage($image)); // CPU intensivo
}
// Mesmo tempo que sequencial!

// ✅ MELHOR - Use processos separados ou queue
```

**Por quê?** Corrotinas compartilham a mesma CPU. Se o gargalo é CPU, não há ganho.

---

#### 2. **Tarefas que dependem de ordem estrita**

**Cenário:** Processamento onde o passo 2 precisa do resultado do passo 1.

```php
// ❌ NÃO FUNCIONA - Ordem importa!
Coroutine::create(fn() => $step1 = processStep1());
Coroutine::create(fn() => $step2 = processStep2($step1)); // ERRO! $step1 pode não existir
Coroutine::create(fn() => $step3 = processStep3($step2)); // ERRO!

// ✅ CORRETO - Sequencial mesmo
$step1 = processStep1();
$step2 = processStep2($step1);
$step3 = processStep3($step2);
```

**Analogia:** Não dá para pintar o teto antes de construir a parede!

---

#### 3. **Operações com efeitos colaterais compartilhados**

**Cenário:** Múltiplas corrotinas escrevendo no mesmo arquivo/variável.

```php
// ❌ PERIGO - Race condition!
$counter = 0;
foreach ($items as $item) {
    Coroutine::create(function () use (&$counter) {
        $counter++; // Várias corrotinas acessam mesma variável!
    });
}
// Resultado imprevisível!

// ✅ CORRETO - Use Channel ou Lock
$channel = new Channel();
foreach ($items as $item) {
    Coroutine::create(fn() => $channel->push(1));
}
$counter = array_sum($channel->pop());
```

---

#### 4. **Código crítico que requer transação atômica**

**Cenário:** Transferência bancária, decremento de estoque.

```php
// ❌ PERIGO - Não use com operações financeiras!
Coroutine::create(fn() => DB::transaction(fn() => debit($account1, 100)));
Coroutine::create(fn() => DB::transaction(fn() => debit($account2, 100)));
// Pode causar inconsistência!

// ✅ CORRETO - Sequencial ou use locks adequados
DB::transaction(function () use ($account1, $account2) {
    debit($account1, 100);
    debit($account2, 100);
});
```

---

### 📊 Tabela de Decisão

| Situação | Paralelizar? | Por quê |
|----------|--------------|---------|
| 3+ requisições HTTP independentes | ✅ Sim | Ganho de 2-5x |
| 10+ queries de banco independentes | ✅ Sim | Ganho de 3-10x |
| Enviar 50+ emails | ✅ Sim | Ganho de 10-50x |
| Processar imagem/vídeo | ❌ Não | CPU-bound |
| Passo 2 depende do passo 1 | ❌ Não | Ordem importa |
| Escrever no mesmo arquivo | ❌ Não | Race condition |
| Transação financeira | ❌ Não | Requer atomicidade |
| 1-2 operações rápidas | ⚠️ Talvez | Overhead > ganho |

---

### 🧪 Teste Prático: Vale a pena paralelizar?

**Regra dos 3 R's:**

1. **Requisições** - São 3+ operações?
2. **Rede/IO** - São operações de I/O (não CPU)?
3. **Independentes** - Uma não depende da outra?

Se **SIM** para os 3 → **Paralelize!** 🚀

**Exemplo:**

```
Buscar 5 produtos de APIs diferentes:
✅ 5 requisições (≥3)
✅ HTTP/Rede (I/O)
✅ Independentes (um não precisa do outro)
→ PARALELIZE! Ganho de ~4x
```

**Contra-exemplo:**

```
Processar 1 imagem:
❌ 1 operação (<3)
→ Não vale o overhead
```

---

## ⚠️ Cuidados e Boas Práticas

### 1. Race Conditions

```php
// ❌ ERRADO - Condição de corrida
$counter = 0;
foreach ($items as $item) {
    Coroutine::create(function () use ($item, &$counter) {
        $counter++; // Problema! Múltiplas corrotinas acessam mesma variável
    });
}

// ✅ CERTO - Use Channel ou Lock
$channel = new Channel();
foreach ($items as $item) {
    Coroutine::create(function () use ($item, $channel) {
        $channel->push(1);
    });
}
```

### 2. Limite de Corrotinas

```php
// ✅ Use um pool/semáforo para limitar
$semaphore = new Coroutine\Semaphore(10); // Máximo 10 corrotinas

foreach ($tasks as $task) {
    $semaphore->push();
    Coroutine::create(function () use ($semaphore, $task) {
        try {
            // Executa tarefa
        } finally {
            $semaphore->pop();
        }
    });
}
```

### 3. Timeout em Corrotinas

```php
// ✅ Adicione timeout para evitar espera infinita
Coroutine::create(function () {
    $channel = new Channel();
    
    Coroutine::create(function () use ($channel) {
        // Tarefa demorada
        $channel->push($result);
    });
    
    // Timeout de 5 segundos
    $result = $channel->pop(5.0);
    if ($result === false) {
        throw new \RuntimeException('Timeout!');
    }
});
```

---

## 📊 Performance na Prática

| Cenário | Sequencial | Concorrente | Ganho |
|---------|------------|-------------|-------|
| 3 requisições HTTP | ~340ms | ~150ms | **2.3x** |
| 10 requisições HTTP | ~1100ms | ~160ms | **6.9x** |
| 5 queries DB | ~500ms | ~120ms | **4.2x** |
| 20 arquivos | ~2000ms | ~200ms | **10x** |

*Valores aproximados - variam conforme rede/hardware*

---

## 🔗 Links Relacionados

- [Rotas e Controllers](rotas-controllers.md)
- [Middlewares](middlewares.md)
- [Configurações](configuracoes.md)

---

## 📖 Links Úteis

- [Hyperf Coroutine Documentation](https://hyperf.wiki/3.1/en/coroutine/coroutine)
- [Swoole Coroutine Guide](https://www.swoole.co.uk/docs/coroutine)
- [WaitGroup Example](https://hyperf.wiki/3.1/en/coroutine/waitgroup)
- [Channel Example](https://hyperf.wiki/3.1/en/coroutine/channel)

---

## 🧪 Teste Você Mesmo

```bash
# Teste sequencial
curl http://localhost:9501/parallelism/sequential

# Teste concorrente
curl http://localhost:9501/parallelism/concurrent

# Comparação completa
curl http://localhost:9501/parallelism/compare
```

Execute várias vezes e observe a variação nos tempos!
