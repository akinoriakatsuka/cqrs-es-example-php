<?php

declare(strict_types=1);

namespace Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Integration Test用の基底クラス
 *
 * MySQLへの接続を提供し、各テストの前後でテーブルをクリーンアップします。
 */
abstract class IntegrationTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        // MySQL接続を作成
        $this->pdo = $this->createMySQLConnection();

        // テーブルをクリーンアップ（外部キー制約があるため順序に注意）
        $this->cleanupTables();
    }

    protected function tearDown(): void
    {
        // テスト後もクリーンアップ
        $this->cleanupTables();

        parent::tearDown();
    }

    /**
     * MySQL接続を作成
     */
    private function createMySQLConnection(): PDO
    {
        $host = getenv('DB_HOST') ?: 'mysql-local';
        $port = getenv('DB_PORT') ?: '3306';
        $database = getenv('DB_DATABASE') ?: 'ceer';
        $username = getenv('DB_USERNAME') ?: 'ceer';
        $password = getenv('DB_PASSWORD') ?: 'ceer';

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $pdo;
    }

    /**
     * テーブルをクリーンアップ
     *
     * 外部キー制約があるため、削除順序に注意が必要
     */
    private function cleanupTables(): void
    {
        // 外部キーチェックを一時的に無効化
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        // テーブルをTRUNCATE（データを全削除）
        $tables = ['messages', 'members', 'group_chats', 'rmu_checkpoint'];

        foreach ($tables as $table) {
            try {
                $this->pdo->exec("TRUNCATE TABLE `{$table}`");
            } catch (\PDOException $e) {
                // テーブルが存在しない場合は無視
                if ($e->getCode() !== '42S02') {
                    throw $e;
                }
            }
        }

        // 外部キーチェックを再度有効化
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}
