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

### ✅ Casos Ideais

- **Múltiplas requisições HTTP/APIs**
- **Queries de banco independentes**
- **Leitura/escrita de arquivos**
- **Processamento de filas**
- **Operações I/O-bound em geral**

### ❌ Quando Evitar

- **Operações CPU-bound intensivas**
- **Tarefas que dependem de ordem estrita**
- **Operações com efeitos colaterais compartilhados**
- **Código crítico que requer transação atômica**

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
