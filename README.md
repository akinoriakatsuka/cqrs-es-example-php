# cqrs-es-example-php

[![CI](https://github.com/akinoriakatsuka/cqrs-es-example-php/actions/workflows/ci.yml/badge.svg)](https://github.com/akinoriakatsuka/cqrs-es-example-php/actions/workflows/ci.yml)
[![Renovate](https://img.shields.io/badge/renovate-enabled-brightgreen.svg)](https://renovatebot.com)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## 概要

PHP実装によるCQRS（Command Query Responsibility Segregation）とイベントソーシングのサンプルプロジェクトです。このプロジェクトは、アクターモデルではなくクラスベースの実装となっています。

本プロジェクトは [j5ik2o/cqrs-es-example-go](https://github.com/j5ik2o/cqrs-es-example-go) のPHP版実装であり、イベントソーシングには [j5ik2o/event-store-adapter-php](https://github.com/j5ik2o/event-store-adapter-php) を使用しています。

他言語での実装例については [j5ik2o/cqrs-es-example](https://github.com/j5ik2o/cqrs-es-example) を参照してください。

## スタック

このOSSリポジトリは、主に以下の技術スタックを利用しています。

- PHP 8.5
- [webonyx/graphql-php](https://github.com/webonyx/graphql-php)
- [j5ik2o/event-store-adapter-php](https://github.com/j5ik2o/event-store-adapter-php)

## システムアーキテクチャ図

![](docs/images/system-layout.png)
## プロジェクト構成

```
.
├── src/
│   ├── Command/                 # コマンド側（書き込み）
│   │   ├── Domain/             # ドメインロジック
│   │   │   ├── GroupChat/      # グループチャット集約
│   │   │   └── UserAccount/    # ユーザーアカウント集約
│   │   ├── InterfaceAdaptor/   # インターフェースアダプター
│   │   │   ├── Repository/     # リポジトリ実装
│   │   │   └── GraphQL/        # GraphQL Mutation Resolver
│   │   └── Processor/          # コマンドプロセッサー
│   ├── Query/                  # クエリ側（読み取り）
│   │   ├── Domain/             # ReadModel定義
│   │   └── InterfaceAdaptor/   # インターフェースアダプター
│   │       ├── Repository/     # Query Repository実装
│   │       └── GraphQL/        # GraphQL Query Resolver
│   └── Rmu/                    # Read Model Updater
│       ├── EventHandlers/      # イベントハンドラー
│       └── DAO/                # データアクセスオブジェクト
├── public/                     # APIエンドポイント
│   ├── read-api-server.php    # Query側GraphQL APIサーバー
│   └── write-api-server.php   # Command側GraphQL APIサーバー
├── bin/                        # CLIツール
│   └── read-model-updater-streams.php  # RMUプロセス
├── tests/                      # テストコード
├── docker/                     # Docker設定ファイル
│   ├── app/                   # アプリケーションコンテナ
│   ├── php-fpm/               # PHP-FPMコンテナ
│   ├── rmu/                   # Read Model Updaterコンテナ
│   └── nginx/                 # Nginx設定
├── tools/                      # 開発ツール
│   └── e2e-test/              # E2Eテストツール
└── docs/                       # ドキュメント
```

## クイックスタート

### 前提条件

- Docker および Docker Compose
- Make (オプション)

### セットアップ

1. リポジトリのクローン
```bash
git clone https://github.com/akinoriakatsuka/cqrs-es-example-php.git
cd cqrs-es-example-php
```

2. Docker環境の起動
```bash
# Dockerイメージのビルド
make docker-compose-build

# サービスの起動
make docker-compose-up
```

3. 動作確認
```bash
# GraphQLエンドポイントのテスト
./tools/e2e-test/verify-group-chat.sh
```

## APIエンドポイント

プロジェクトには2つのGraphQL APIエンドポイントがあります：

### Write API (Command側)
- **URL**: `http://localhost:18080`
- **GraphQL Endpoint**: `http://localhost:18080/query`
- **GraphQL Playground**: `http://localhost:18080/`
- **実装**: `public/write-api-server.php`
- **役割**: データの作成・更新・削除（Mutation）

### Read API (Query側)
- **URL**: `http://localhost:18000`
- **GraphQL Endpoint**: `http://localhost:18000/query`
- **GraphQL Playground**: `http://localhost:18000/`
- **実装**: `public/read-api-server.php`
- **役割**: データの読み取り（Query）

### Read Model Updater (RMU)
- **実装**: `bin/read-model-updater-streams.php`
- **役割**: DynamoDB Streamsからイベントを読み取り、MySQLのReadModelを更新
- **起動**: Dockerコンテナ `rmu` で自動起動

## 開発

### コマンド実行

すべてのコマンドはDockerコンテナ内で実行します：

```bash
# アプリケーションコンテナ内でコマンド実行
docker compose exec app [コマンド]
```

### テスト

```bash
# 全テスト実行
make test

# カバレッジ付きテスト
make test-coverage

# 特定のテストファイル実行
docker compose exec app vendor/bin/phpunit tests/Path/To/TestFile.php
```

### コード品質

```bash
# コードフォーマット
make fmt

# 静的解析
make phpstan

# リント（フォーマット + 静的解析）
make lint
```

## 設計原則

### CQRS (Command Query Responsibility Segregation)

本プロジェクトでは、コマンド（書き込み）とクエリ（読み取り）の責務を明確に分離しています：

- **Command側**: データの変更を担当。イベントソーシングにより全ての変更をイベントとして記録
- **Query側**: データの読み取りを担当。最適化された読み取り専用モデル（ReadModel）を使用
- **RMU (Read Model Updater)**: DynamoDB StreamsからイベントをポーリングしてReadModelを更新

### Event Sourcing

すべてのドメインイベントは以下の特徴を持ちます：

- イミュータブル（不変）
- 順序付けられている
- 集約の完全な状態を再構築可能
- DynamoDB Streamsを通じてRMUに配信される

### Domain-Driven Design (DDD)

- **集約 (Aggregate)**: GroupChat, UserAccount
- **値オブジェクト (Value Object)**: GroupChatId, UserAccountId, MemberId
- **ドメインイベント**: GroupChatCreated, MemberAdded, MessagePosted, etc.
- **ReadModel**: GroupChatReadModel, MemberReadModel, MessageReadModel

## Contributing

プルリクエストを歓迎します。大きな変更の場合は、まずissueを開いて変更内容について議論してください。

### 開発フロー

1. Featureブランチを作成 (`git checkout -b feature/AmazingFeature`)
2. 変更をコミット (`git commit -m 'Add some AmazingFeature'`)
3. ブランチをプッシュ (`git push origin feature/AmazingFeature`)
4. プルリクエストを作成

## 関連プロジェクト

- [j5ik2o/cqrs-es-example](https://github.com/j5ik2o/cqrs-es-example) - 他言語での実装例
- [j5ik2o/cqrs-es-example-go](https://github.com/j5ik2o/cqrs-es-example-go) - Go実装（本プロジェクトの参照実装）
- [j5ik2o/event-store-adapter-php](https://github.com/j5ik2o/event-store-adapter-php) - PHP向けイベントストアアダプター

## ライセンス

このプロジェクトはMITライセンスの下で公開されています。詳細は[LICENSE](LICENSE)ファイルを参照してください。

## Author

- Akinori Takigawa ([@akinoriakatsuka](https://github.com/akinoriakatsuka))
- Junichi Kato ([@j5ik2o](https://github.com/j5ik2o))

## サポート

質問や問題がある場合は、[Issues](https://github.com/akinoriakatsuka/cqrs-es-example-php/issues)で報告してください。