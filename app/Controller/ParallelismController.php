<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\WaitGroup;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\Guzzle\ClientFactory;

/**
 * Controller de exemplo para demonstrar paralelismo e corrotinas no Hyperf
 *
 * Compara execuções sequenciais vs paralelas usando corrotinas do Swoole
 */
#[Controller]
class ParallelismController extends AbstractController
{
    public function __construct(
        private ClientFactory $clientFactory
    ) {
    }

    /**
     * Rota SEQUENCIAL - Faz 3 requisições uma após a outra
     *
     * Tempo total = soma dos tempos de cada requisição
     *
     * @return array
     */
    #[GetMapping(path: '/parallelism/sequential')]
    public function sequential(): array
    {
        $startTime = microtime(true);
        $results = [];
        $urls = [
            'https://jsonplaceholder.typicode.com/posts/1',
            'https://jsonplaceholder.typicode.com/posts/2',
            'https://jsonplaceholder.typicode.com/posts/3',
        ];

        $client = $this->clientFactory->create();

        foreach ($urls as $index => $url) {
            $requestStart = microtime(true);

            // Faz a requisição HTTP
            $response = $client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            $requestEnd = microtime(true);
            $requestTime = round(($requestEnd - $requestStart) * 1000, 2);

            $results[] = [
                'request' => $index + 1,
                'url' => $url,
                'data' => $data,
                'time_ms' => $requestTime,
            ];
        }

        $endTime = microtime(true);
        $totalTime = round(($endTime - $startTime) * 1000, 2);

        $sumOfRequests = array_sum(array_column($results, 'time_ms'));

        return [
            'type' => 'SEQUENTIAL',
            'description' => 'Requisições executadas uma após a outra (bloqueante)',
            'results' => $results,
            'total_time_ms' => $totalTime,
            'sum_of_requests_ms' => $sumOfRequests,
            'overhead_ms' => round($totalTime - $sumOfRequests, 2),
        ];
    }

    /**
     * Rota PARALELA - Faz 3 requisições simultaneamente usando corrotinas
     *
     * Tempo total ≈ tempo da requisição mais lenta
     *
     * @return array
     */
    #[GetMapping(path: '/parallelism/concurrent')]
    public function concurrent(): array
    {
        $startTime = microtime(true);

        $urls = [
            'https://jsonplaceholder.typicode.com/posts/1',
            'https://jsonplaceholder.typicode.com/posts/2',
            'https://jsonplaceholder.typicode.com/posts/3',
        ];

        $results = [];
        $waitGroup = new WaitGroup();

        // Cria uma corrotina para cada requisição
        foreach ($urls as $index => $url) {
            Coroutine::create(function () use ($waitGroup, $index, $url, &$results) {
                $waitGroup->add();

                try {
                    $requestStart = microtime(true);

                    $client = $this->clientFactory->create();
                    $response = $client->get($url);
                    $data = json_decode($response->getBody()->getContents(), true);

                    $requestEnd = microtime(true);
                    $requestTime = round(($requestEnd - $requestStart) * 1000, 2);

                    $results[$index] = [
                        'request' => $index + 1,
                        'url' => $url,
                        'data' => $data,
                        'time_ms' => $requestTime,
                    ];
                } finally {
                    $waitGroup->done();
                }
            });
        }

        // Aguarda todas as corrotinas terminarem
        $waitGroup->wait();

        $endTime = microtime(true);
        $totalTime = round(($endTime - $startTime) * 1000, 2);

        // Ordena resultados pela ordem original
        ksort($results);
        $results = array_values($results);

        $sumOfRequests = array_sum(array_column($results, 'time_ms'));

        return [
            'type' => 'CONCURRENT',
            'description' => 'Requisições executadas em paralelo com corrotinas (não-bloqueante)',
            'results' => $results,
            'total_time_ms' => $totalTime,
            'sum_of_requests_ms' => $sumOfRequests,
            'time_saved_ms' => round($sumOfRequests - $totalTime, 2),
            'speedup_percentage' => round((1 - ($totalTime / $sumOfRequests)) * 100, 2) . '%',
        ];
    }

    /**
     * Rota de comparação - Executa ambas as abordagens e compara
     *
     * @return array
     */
    #[GetMapping(path: '/parallelism/compare')]
    public function compare(): array
    {
        $urls = [
            'https://jsonplaceholder.typicode.com/posts/1',
            'https://jsonplaceholder.typicode.com/posts/2',
            'https://jsonplaceholder.typicode.com/posts/3',
        ];

        // Executa sequencial
        $sequentialStart = microtime(true);
        $sequentialResult = $this->executeSequential($urls);
        $sequentialEnd = microtime(true);
        $sequentialTotal = round(($sequentialEnd - $sequentialStart) * 1000, 2);

        // Executa concorrente
        $concurrentStart = microtime(true);
        $concurrentResult = $this->executeConcurrent($urls);
        $concurrentEnd = microtime(true);
        $concurrentTotal = round(($concurrentEnd - $concurrentStart) * 1000, 2);

        return [
            'comparison' => [
                'sequential' => [
                    'total_time_ms' => $sequentialTotal,
                    'requests' => $sequentialResult,
                ],
                'concurrent' => [
                    'total_time_ms' => $concurrentTotal,
                    'requests' => $concurrentResult,
                ],
                'difference_ms' => round($sequentialTotal - $concurrentTotal, 2),
                'speedup' => round($sequentialTotal / max($concurrentTotal, 1), 2) . 'x mais rápido',
                'time_saved_percentage' => round((1 - ($concurrentTotal / max($sequentialTotal, 1))) * 100, 2) . '%',
            ],
            'explanation' => [
                'sequential' => 'Executa uma requisição por vez. Tempo total = soma de todos os tempos.',
                'concurrent' => 'Executa todas as requisições simultaneamente. Tempo total ≈ tempo da mais lenta.',
                'benefit' => 'Quanto mais requisições I/O-bound, maior o ganho com corrotinas.',
            ],
        ];
    }

    /**
     * Executa requisições sequenciais (helper para compare)
     */
    private function executeSequential(array $urls): array
    {
        $client = $this->clientFactory->create();
        $results = [];

        foreach ($urls as $index => $url) {
            $start = microtime(true);
            $response = $client->get($url);
            $end = microtime(true);

            $results[] = [
                'request' => $index + 1,
                'time_ms' => round(($end - $start) * 1000, 2),
            ];
        }

        return $results;
    }

    /**
     * Executa requisições concorrentes (helper para compare)
     */
    private function executeConcurrent(array $urls): array
    {
        $results = [];
        $waitGroup = new WaitGroup();

        foreach ($urls as $index => $url) {
            Coroutine::create(function () use ($waitGroup, $index, $url, &$results) {
                $waitGroup->add();

                try {
                    $start = microtime(true);
                    $client = $this->clientFactory->create();
                    $response = $client->get($url);
                    $end = microtime(true);

                    $results[$index] = [
                        'request' => $index + 1,
                        'time_ms' => round(($end - $start) * 1000, 2),
                    ];
                } finally {
                    $waitGroup->done();
                }
            });
        }

        $waitGroup->wait();
        ksort($results);

        return array_values($results);
    }
}
