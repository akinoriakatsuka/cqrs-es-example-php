#!/bin/bash

# GraphQL E2E Test Script
# このスクリプトはGraphQLエンドポイントのE2Eテストを行います

set -e

# 設定
GRAPHQL_ENDPOINT="http://localhost:8081/graphql"
TEST_NAME="GraphQL E2E Test"

echo "=== $TEST_NAME ==="
echo "Endpoint: $GRAPHQL_ENDPOINT"
echo

# ヘルスチェック
echo "1. Health Check..."
HEALTH_RESPONSE=$(curl -s -w "%{http_code}" -o /tmp/health_response.json "$GRAPHQL_ENDPOINT/../health")
HEALTH_STATUS_CODE=${HEALTH_RESPONSE: -3}

if [ "$HEALTH_STATUS_CODE" = "200" ]; then
    echo "✅ Health check passed"
    cat /tmp/health_response.json | jq .
else
    echo "❌ Health check failed (Status: $HEALTH_STATUS_CODE)"
    exit 1
fi

echo

# GraphQL Mutation テスト
echo "2. Testing createGroupChat mutation..."

# テストデータ
GROUP_NAME="Test Group $(date +%s)"
EXECUTOR_ID="test-user-$(date +%s)"

# GraphQL クエリ
QUERY='{
  "query": "mutation CreateGroupChat($name: String!, $executorId: String!) { createGroupChat(name: $name, executorId: $executorId) { id name version isDeleted } }",
  "variables": {
    "name": "'"$GROUP_NAME"'",
    "executorId": "'"$EXECUTOR_ID"'"
  }
}'

echo "Sending mutation with:"
echo "  Group Name: $GROUP_NAME"
echo "  Executor ID: $EXECUTOR_ID"
echo

# GraphQL リクエスト実行
GRAPHQL_RESPONSE=$(curl -s -w "%{http_code}" \
  -H "Content-Type: application/json" \
  -d "$QUERY" \
  -o /tmp/graphql_response.json \
  "$GRAPHQL_ENDPOINT")

GRAPHQL_STATUS_CODE=${GRAPHQL_RESPONSE: -3}

if [ "$GRAPHQL_STATUS_CODE" = "200" ]; then
    echo "✅ GraphQL request successful (Status: $GRAPHQL_STATUS_CODE)"
    
    # レスポンス内容を確認
    echo "Response:"
    cat /tmp/graphql_response.json | jq .
    
    # エラーチェック
    ERROR_COUNT=$(cat /tmp/graphql_response.json | jq '.errors | length' 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" != "null" ] && [ "$ERROR_COUNT" -gt 0 ]; then
        echo "❌ GraphQL errors found:"
        cat /tmp/graphql_response.json | jq '.errors'
        exit 1
    fi
    
    # データの検証
    CREATED_ID=$(cat /tmp/graphql_response.json | jq -r '.data.createGroupChat.id')
    CREATED_NAME=$(cat /tmp/graphql_response.json | jq -r '.data.createGroupChat.name')
    CREATED_VERSION=$(cat /tmp/graphql_response.json | jq -r '.data.createGroupChat.version')
    CREATED_IS_DELETED=$(cat /tmp/graphql_response.json | jq -r '.data.createGroupChat.isDeleted')
    
    echo
    echo "Created GroupChat:"
    echo "  ID: $CREATED_ID"
    echo "  Name: $CREATED_NAME"
    echo "  Version: $CREATED_VERSION"
    echo "  IsDeleted: $CREATED_IS_DELETED"
    
    # 基本的な検証
    if [ "$CREATED_NAME" = "$GROUP_NAME" ] && [ "$CREATED_VERSION" = "1" ] && [ "$CREATED_IS_DELETED" = "false" ]; then
        echo "✅ Data validation passed"
    else
        echo "❌ Data validation failed"
        exit 1
    fi
    
else
    echo "❌ GraphQL request failed (Status: $GRAPHQL_STATUS_CODE)"
    echo "Response:"
    cat /tmp/graphql_response.json
    exit 1
fi

echo

# 複数のミューテーションテスト
echo "3. Testing multiple mutations..."

for i in {1..3}; do
    echo "  Test $i/3..."
    
    QUERY_MULTI='{
      "query": "mutation CreateGroupChat($name: String!, $executorId: String!) { createGroupChat(name: $name, executorId: $executorId) { id name version isDeleted } }",
      "variables": {
        "name": "Multi Test Group '"$i"' $(date +%s)",
        "executorId": "multi-test-user-'"$i"'-$(date +%s)"
      }
    }'
    
    MULTI_RESPONSE=$(curl -s -w "%{http_code}" \
      -H "Content-Type: application/json" \
      -d "$QUERY_MULTI" \
      -o /tmp/multi_response_$i.json \
      "$GRAPHQL_ENDPOINT")
    
    MULTI_STATUS_CODE=${MULTI_RESPONSE: -3}
    
    if [ "$MULTI_STATUS_CODE" = "200" ]; then
        MULTI_ID=$(cat /tmp/multi_response_$i.json | jq -r '.data.createGroupChat.id')
        echo "    ✅ Created group with ID: $MULTI_ID"
    else
        echo "    ❌ Failed to create group (Status: $MULTI_STATUS_CODE)"
        exit 1
    fi
done

echo

# エラーケースのテスト
echo "4. Testing error cases..."

# 無効なクエリ
echo "  Testing invalid query..."
INVALID_QUERY='{"query": "invalid graphql query"}'

INVALID_RESPONSE=$(curl -s -w "%{http_code}" \
  -H "Content-Type: application/json" \
  -d "$INVALID_QUERY" \
  -o /tmp/invalid_response.json \
  "$GRAPHQL_ENDPOINT")

INVALID_STATUS_CODE=${INVALID_RESPONSE: -3}

if [ "$INVALID_STATUS_CODE" = "200" ]; then
    ERROR_COUNT=$(cat /tmp/invalid_response.json | jq '.errors | length' 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo "    ✅ Invalid query properly rejected with errors"
    else
        echo "    ❌ Invalid query should have errors"
        exit 1
    fi
else
    echo "    ❌ Invalid query test failed (Status: $INVALID_STATUS_CODE)"
    exit 1
fi

# 必須パラメータ不足のテスト
echo "  Testing missing required parameters..."
MISSING_PARAMS_QUERY='{
  "query": "mutation CreateGroupChat($name: String!, $executorId: String!) { createGroupChat(name: $name, executorId: $executorId) { id name version isDeleted } }",
  "variables": {
    "name": "Test Group"
  }
}'

MISSING_RESPONSE=$(curl -s -w "%{http_code}" \
  -H "Content-Type: application/json" \
  -d "$MISSING_PARAMS_QUERY" \
  -o /tmp/missing_response.json \
  "$GRAPHQL_ENDPOINT")

MISSING_STATUS_CODE=${MISSING_RESPONSE: -3}

if [ "$MISSING_STATUS_CODE" = "200" ]; then
    ERROR_COUNT=$(cat /tmp/missing_response.json | jq '.errors | length' 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo "    ✅ Missing parameters properly rejected with errors"
    else
        echo "    ❌ Missing parameters should have errors"
        exit 1
    fi
else
    echo "    ❌ Missing parameters test failed (Status: $MISSING_STATUS_CODE)"
    exit 1
fi

echo

# クリーンアップ
echo "Cleaning up temporary files..."
rm -f /tmp/health_response.json
rm -f /tmp/graphql_response.json
rm -f /tmp/multi_response_*.json
rm -f /tmp/invalid_response.json
rm -f /tmp/missing_response.json

echo
echo "=== $TEST_NAME COMPLETED SUCCESSFULLY ==="
echo "✅ All tests passed!"