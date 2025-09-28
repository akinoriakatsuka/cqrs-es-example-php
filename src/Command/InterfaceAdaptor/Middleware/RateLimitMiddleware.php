<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * シンプルなレート制限ミドルウェア
 * 本格的な実装ではRedisやMemcachedを使用することを推奨
 */
final class RateLimitMiddleware implements MiddlewareInterface {
    /** @var array<string, array<int>> */
    private static array $requests = [];

    public function __construct(
        private readonly int $max_requests = 60,
        private readonly int $window_seconds = 60
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $client_ip = $this->getClientIp($request);
        $current_time = time();

        // 古いリクエスト記録をクリーンアップ
        $this->cleanupOldRequests($current_time);

        // クライアントのリクエスト履歴を取得
        if (!isset(self::$requests[$client_ip])) {
            self::$requests[$client_ip] = [];
        }

        $client_requests = self::$requests[$client_ip];

        // 現在のウィンドウ内のリクエスト数をカウント
        $window_start = $current_time - $this->window_seconds;
        $requests_in_window = array_filter($client_requests, fn ($timestamp) => $timestamp > $window_start);

        // レート制限チェック
        if (count($requests_in_window) >= $this->max_requests) {
            $response = new Response();
            $json_content = json_encode([
                'errors' => [
                    [
                        'message' => 'Rate limit exceeded. Too many requests.',
                        'extensions' => [
                            'category' => 'rate_limit',
                            'max_requests' => $this->max_requests,
                            'window_seconds' => $this->window_seconds,
                        ],
                    ],
                ],
            ]);
            if ($json_content !== false) {
                $response->getBody()->write($json_content);
            }

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('X-RateLimit-Limit', (string)$this->max_requests)
                ->withHeader('X-RateLimit-Remaining', '0')
                ->withHeader('X-RateLimit-Reset', (string)($current_time + $this->window_seconds))
                ->withStatus(429);
        }

        // リクエストを記録
        self::$requests[$client_ip][] = $current_time;

        // レスポンスヘッダーにレート制限情報を追加
        $response = $handler->handle($request);
        $remaining = $this->max_requests - count($requests_in_window) - 1;

        return $response
            ->withHeader('X-RateLimit-Limit', (string)$this->max_requests)
            ->withHeader('X-RateLimit-Remaining', (string)max(0, $remaining))
            ->withHeader('X-RateLimit-Reset', (string)($current_time + $this->window_seconds));
    }

    private function getClientIp(ServerRequestInterface $request): string {
        // プロキシを考慮したIP取得
        $server_params = $request->getServerParams();

        if (!empty($server_params['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $server_params['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        if (!empty($server_params['HTTP_X_REAL_IP'])) {
            return $server_params['HTTP_X_REAL_IP'];
        }

        return $server_params['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private function cleanupOldRequests(int $current_time): void {
        $window_start = $current_time - $this->window_seconds;

        foreach (self::$requests as $ip => $timestamps) {
            self::$requests[$ip] = array_filter($timestamps, fn ($timestamp) => $timestamp > $window_start);

            // 空の配列は削除
            if (empty(self::$requests[$ip])) {
                unset(self::$requests[$ip]);
            }
        }
    }
}
