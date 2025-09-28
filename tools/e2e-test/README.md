# E2E Test Tools

このディレクトリには、GraphQLエンドポイントのE2Eテストを行うためのツールが含まれています。

## テストスクリプト

### test-graphql-endpoint.sh

GraphQLエンドポイントの包括的なE2Eテストを実行します。

#### 機能

1. **ヘルスチェック**: `/health` エンドポイントの動作確認
2. **createGroupChat mutation**: グループチャット作成機能のテスト
3. **複数ミューテーション**: 連続実行での安定性確認
4. **エラーケース**: 無効なクエリや必須パラメータ不足の処理確認

#### 使用方法

```bash
# サーバーが起動している状態で実行
./tools/e2e-test/test-graphql-endpoint.sh
```

#### 前提条件

- Docker環境が起動していること (`docker compose up -d`)
- `jq` コマンドがインストールされていること
- GraphQLエンドポイントが `http://localhost:8081/graphql` で利用可能であること

#### テスト内容詳細

1. **ヘルスチェックテスト**
   - `/health` エンドポイントへのGETリクエスト
   - レスポンスステータスコードの確認
   - レスポンスJSONの形式確認

2. **基本ミューテーションテスト**
   - `createGroupChat` ミューテーションの実行
   - レスポンスデータの検証（ID、名前、バージョン、削除フラグ）
   - ULIDフォーマットの確認

3. **複数ミューテーションテスト**
   - 3回連続でのミューテーション実行
   - 各実行の独立性確認
   - ID重複がないことの確認

4. **エラーハンドリングテスト**
   - 無効なGraphQLクエリに対するエラーレスポンス
   - 必須パラメータ不足時のエラーレスポンス
   - エラーメッセージの適切性確認

#### 出力例

```
=== GraphQL E2E Test ===
Endpoint: http://localhost:8081/graphql

1. Health Check...
✅ Health check passed
{
  "status": "OK",
  "timestamp": "2023-01-01T12:00:00+00:00"
}

2. Testing createGroupChat mutation...
Sending mutation with:
  Group Name: Test Group 1672574400
  Executor ID: test-user-1672574400

✅ GraphQL request successful (Status: 200)
Response:
{
  "data": {
    "createGroupChat": {
      "id": "01ARZ3NDEKTSV4RRFFQ69G5FAV",
      "name": "Test Group 1672574400",
      "version": 1,
      "isDeleted": false
    }
  }
}

Created GroupChat:
  ID: 01ARZ3NDEKTSV4RRFFQ69G5FAV
  Name: Test Group 1672574400
  Version: 1
  IsDeleted: false
✅ Data validation passed

3. Testing multiple mutations...
  Test 1/3...
    ✅ Created group with ID: 01ARZ3NDEKTSV4RRFFQ69G5FBW
  Test 2/3...
    ✅ Created group with ID: 01ARZ3NDEKTSV4RRFFQ69G5FCX
  Test 3/3...
    ✅ Created group with ID: 01ARZ3NDEKTSV4RRFFQ69G5FDY

4. Testing error cases...
  Testing invalid query...
    ✅ Invalid query properly rejected with errors
  Testing missing required parameters...
    ✅ Missing parameters properly rejected with errors

Cleaning up temporary files...

=== GraphQL E2E Test COMPLETED SUCCESSFULLY ===
✅ All tests passed!
```

## 依存関係

- `curl`: HTTPリクエストの送信
- `jq`: JSONレスポンスの解析
- `bash`: スクリプト実行環境