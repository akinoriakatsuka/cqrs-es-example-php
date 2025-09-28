# CQRS/Event Sourcing Example PHP プロジェクト

このプロジェクトは、PHPでCQRS（Command Query Responsibility Segregation）とEvent Sourcingの実装例を示すプロジェクトです。

## プロジェクト概要

- **参照実装**: https://github.com/j5ik2o/cqrs-es-example-go のPHP版
- **アーキテクチャ**: CQRS + Event Sourcing
- **主要技術**: PHP 8.2+, GraphQL, DynamoDB, Docker

## 開発環境構築

### Docker環境
すべてのコマンドはDockerコンテナ内で実行してください。

```bash
# コンテナ起動
docker compose up -d

# アプリケーションコンテナ内でのコマンド実行
docker compose exec app [コマンド]
```

## 開発規約

### コードフォーマット・静的解析

```bash
# コードフォーマット
docker compose exec app composer cs:fix

# 静的解析
docker compose exec app composer phpstan

# リント（フォーマット + 静的解析）
docker compose exec app composer lint
```

### テスト実行

```bash
# 全テスト実行
docker compose exec app composer test

# カバレッジ付きテスト
docker compose exec app composer test:coverage

# 特定のテストファイル実行
docker compose exec app vendor/bin/phpunit tests/Path/To/TestFile.php --testdox
```

### 命名規則

- **変数名**: スネークケース（snake_case）を使用
- **クラス名**: パスカルケース（PascalCase）
- **メソッド名**: キャメルケース（camelCase）

```php
// Good
$group_chat_id = new GroupChatId();
$user_account = $this->findUserAccount($user_id);

// Bad
$groupChatId = new GroupChatId();
$userAccount = $this->findUserAccount($userId);
```

### TDD（Test Driven Development）

新機能実装時は必ずRed-Green-Refactorサイクルに従ってください。

1. **Red**: テストを先に書いてfailすることを確認
2. **Green**: テストが通る最小限のコードを実装
3. **Refactor**: 必要に応じてコードを改善

### データプロバイダーのテスト規約

```php
/**
 * @dataProvider testMethodNameProvider
 */
public function testMethodName(...): void {
    // テスト実装
}

// データプロバイダーメソッドはテストメソッドの直下に配置
public function testMethodNameProvider(): array {
    return [
        'case name' => [
            // テストデータ
        ],
    ];
}
```

## アーキテクチャ

### CQRS原則

- **Command側**: データの変更（Mutation）のみ
- **Query側**: データの読み取り（Query）のみ

### ディレクトリ構造

```
src/
├── Command/              # Command側（書き込み）
│   ├── Domain/          # ドメインロジック
│   ├── InterfaceAdaptor/ # GraphQL Mutation、Repository実装
│   └── Processor/       # コマンド処理
└── Query/               # Query側（読み取り）※今後実装予定
    └── InterfaceAdaptor/ # GraphQL Query
```

### GraphQL実装

- **Command側**: MutationResolverのみ実装
- **Query側**: QueryResolverを実装予定（別途Query側の実装が必要）

```php
// Command側のMutationResolver例
public function createGroupChat(mixed $root_value, array $args): array {
    // バリデーション
    if (!is_string($args['name'])) {
        throw new \InvalidArgumentException('Name must be a string');
    }
    
    // ドメインロジック実行
    $event = $this->command_processor->createGroupChat($name, $executor_id);
    
    return $result;
}
```

## プルリクエスト作成時の注意事項

- **言語**: タイトルと本文は日本語で記述する
- **コミットメッセージ**: "by Claude Code" を含める
- **テンプレート**: `.github/PULL_REQUEST_TEMPLATE.md` があれば使用する
- **チェック項目**:
  - [ ] 全テストが通ることを確認
  - [ ] PHPStanエラーがないことを確認  
  - [ ] コードフォーマットを適用済み
  - [ ] 関連するテストを追加・修正した場合のみチェック

## 利用可能なComposerスクリプト

```bash
composer test        # テスト実行
composer test:coverage # カバレッジ付きテスト
composer cs          # コードスタイルチェック（dry-run）
composer cs:fix      # コードフォーマット実行
composer fmt         # cs:fixのエイリアス
composer lint        # cs + phpstan
composer phpstan     # 静的解析
```

## 参考リンク

- [Go版リファレンス実装](https://github.com/j5ik2o/cqrs-es-example-go)
- [Event Store Adapter PHP](https://github.com/j5ik2o/event-store-adapter-php)