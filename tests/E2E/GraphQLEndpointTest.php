<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\E2E;

use PHPUnit\Framework\TestCase;

/**
 * GraphQLエンドポイントのE2Eテスト
 */
final class GraphQLEndpointTest extends TestCase {
    private const GRAPHQL_ENDPOINT = 'http://localhost:8081/graphql';
    private const HEALTH_ENDPOINT = 'http://localhost:8081/health';

    protected function setUp(): void {
        // サーバーが起動していることを確認
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Server is not running on ' . self::GRAPHQL_ENDPOINT);
        }
    }

    public function testHealthEndpoint(): void {
        $response = $this->makeRequest('GET', self::HEALTH_ENDPOINT);

        $this->assertEquals(200, $response['status_code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('status', $response['body']);
        $this->assertEquals('OK', $response['body']['status']);
        $this->assertArrayHasKey('timestamp', $response['body']);
    }

    public function testSuccessfulGraphQLMutation(): void {
        $query = [
            'query' => 'mutation CreateGroupChat($name: String!, $executorId: String!) { 
                createGroupChat(name: $name, executorId: $executorId) { 
                    id name version isDeleted 
                } 
            }',
            'variables' => [
                'name' => 'Test Group ' . time(),
                'executorId' => 'test-user-' . time(),
            ],
        ];

        $response = $this->makeGraphQLRequest($query);

        $this->assertEquals(200, $response['status_code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertIsArray($response['body']['data']);
        $this->assertArrayHasKey('createGroupChat', $response['body']['data']);

        $result = $response['body']['data']['createGroupChat'];
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals(1, $result['version']);
        $this->assertFalse($result['isDeleted']);
    }

    public function testInvalidContentType(): void {
        $response = $this->makeRequest('POST', self::GRAPHQL_ENDPOINT, [
            'Content-Type: text/plain',
        ], '{"query": "{ __typename }"}');

        $this->assertEquals(400, $response['status_code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('errors', $response['body']);
        $this->assertIsArray($response['body']['errors']);
        $this->assertIsArray($response['body']['errors'][0]);
        $this->assertIsString($response['body']['errors'][0]['message']);
        $this->assertStringContainsString('Content-Type must be application/json', $response['body']['errors'][0]['message']);
    }

    public function testEmptyRequestBody(): void {
        $response = $this->makeRequest('POST', self::GRAPHQL_ENDPOINT, [
            'Content-Type: application/json',
        ], '');

        $this->assertEquals(400, $response['status_code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('errors', $response['body']);
        $this->assertIsArray($response['body']['errors']);
        $this->assertIsArray($response['body']['errors'][0]);
        $this->assertIsString($response['body']['errors'][0]['message']);
        $this->assertStringContainsString('Request body cannot be empty', $response['body']['errors'][0]['message']);
    }

    public function testInvalidJSON(): void {
        $response = $this->makeRequest('POST', self::GRAPHQL_ENDPOINT, [
            'Content-Type: application/json',
        ], '{"query": invalid json}');

        $this->assertEquals(400, $response['status_code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('errors', $response['body']);
        $this->assertIsArray($response['body']['errors']);
        $this->assertIsArray($response['body']['errors'][0]);
        $this->assertIsString($response['body']['errors'][0]['message']);
        $this->assertStringContainsString('Invalid JSON', $response['body']['errors'][0]['message']);
    }

    public function testMissingQueryField(): void {
        $response = $this->makeGraphQLRequest(['variables' => []]);

        $this->assertEquals(400, $response['status_code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('errors', $response['body']);
        $this->assertIsArray($response['body']['errors']);
        $this->assertIsArray($response['body']['errors'][0]);
        $this->assertIsString($response['body']['errors'][0]['message']);
        $this->assertStringContainsString('Query field is required', $response['body']['errors'][0]['message']);
    }

    public function testEmptyQuery(): void {
        $response = $this->makeGraphQLRequest(['query' => '']);

        $this->assertEquals(400, $response['status_code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('errors', $response['body']);
        $this->assertIsArray($response['body']['errors']);
        $this->assertIsArray($response['body']['errors'][0]);
        $this->assertIsString($response['body']['errors'][0]['message']);
        $this->assertStringContainsString('Query cannot be empty', $response['body']['errors'][0]['message']);
    }

    public function testInvalidGraphQLQuery(): void {
        $response = $this->makeGraphQLRequest(['query' => 'invalid graphql query']);

        $this->assertEquals(400, $response['status_code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('errors', $response['body']);
    }

    public function testMissingRequiredVariables(): void {
        $query = [
            'query' => 'mutation CreateGroupChat($name: String!, $executorId: String!) { 
                createGroupChat(name: $name, executorId: $executorId) { 
                    id name version isDeleted 
                } 
            }',
            'variables' => [
                'name' => 'Test Group',
                // executorId is missing
            ],
        ];

        $response = $this->makeGraphQLRequest($query);

        $this->assertEquals(400, $response['status_code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('errors', $response['body']);
    }

    public function testCORSHeaders(): void {
        $response = $this->makeRequest('OPTIONS', self::GRAPHQL_ENDPOINT, [
            'Origin: http://localhost:3000',
            'Content-Type: application/json',
        ]);

        $this->assertEquals(200, $response['status_code']);
        $this->assertIsArray($response['headers']);
        $this->assertArrayHasKey('Access-Control-Allow-Origin', $response['headers']);
        $this->assertIsString($response['headers']['Access-Control-Allow-Origin']);
        $this->assertEquals('http://localhost:3000', $response['headers']['Access-Control-Allow-Origin']);
    }

    public function testUnauthorizedOrigin(): void {
        $response = $this->makeRequest('OPTIONS', self::GRAPHQL_ENDPOINT, [
            'Origin: http://malicious-site.com',
            'Content-Type: application/json',
        ]);

        $this->assertEquals(200, $response['status_code']);
        // 不正なオリジンはnullまたは許可されていないはず
        $this->assertIsArray($response['headers']);
        if (isset($response['headers']['Access-Control-Allow-Origin'])) {
            $this->assertIsString($response['headers']['Access-Control-Allow-Origin']);
            $this->assertNotEquals('http://malicious-site.com', $response['headers']['Access-Control-Allow-Origin']);
        }
    }

    /**
     * GraphQLリクエストを送信
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function makeGraphQLRequest(array $data): array {
        return $this->makeRequest('POST', self::GRAPHQL_ENDPOINT, [
            'Content-Type: application/json',
        ], json_encode($data) ?: '');
    }

    /**
     * HTTPリクエストを送信
     * @param array<string> $headers
     * @return array<string, mixed>
     */
    private function makeRequest(string $method, string $url, array $headers = [], string $body = ''): array {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($response === false) {
            $this->fail('Failed to make HTTP request to ' . $url);
        }

        $header_text = is_string($response) ? substr($response, 0, $header_size) : '';
        $body_text = is_string($response) ? substr($response, $header_size) : '';

        // ヘッダーをパース
        $headers_array = [];
        $header_lines = explode("\r\n", $header_text);
        foreach ($header_lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $headers_array[trim($key)] = trim($value);
            }
        }

        // JSONレスポンスをデコード
        $decoded_body = json_decode($body_text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decoded_body = $body_text;
        }

        return [
            'status_code' => $http_code,
            'headers' => $headers_array,
            'body' => $decoded_body,
        ];
    }

    /**
     * サーバーが起動しているかチェック
     */
    private function isServerRunning(): bool {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::HEALTH_ENDPOINT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $result !== false && $http_code === 200;
    }
}
