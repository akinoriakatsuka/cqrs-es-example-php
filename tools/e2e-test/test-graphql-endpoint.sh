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
    # JSON形式を確認してからjqを使用
    if [ -s /tmp/health_response.json ] && head -c 1 /tmp/health_response.json | grep -q '{'; then
        cat /tmp/health_response.json | jq . 2>/dev/null || cat /tmp/health_response.json
    else
        echo "  Response is not valid JSON:"
        cat /tmp/health_response.json
    fi
else
    echo "❌ Health check failed (Status: $HEALTH_STATUS_CODE)"
    cat /tmp/health_response.json
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

# セキュリティとエラーケースのテスト
echo "4. Testing security and error cases..."

# Content-Type検証テスト
echo "  Testing Content-Type validation..."
WRONG_CONTENT_TYPE_RESPONSE=$(curl -s -w "%{http_code}" \
  -H "Content-Type: text/plain" \
  -d '{"query": "{ __typename }"}' \
  -o /tmp/wrong_content_type_response.json \
  "$GRAPHQL_ENDPOINT")

WRONG_CONTENT_TYPE_STATUS=${WRONG_CONTENT_TYPE_RESPONSE: -3}

if [ "$WRONG_CONTENT_TYPE_STATUS" = "400" ]; then
    ERROR_COUNT=$(cat /tmp/wrong_content_type_response.json | jq '.errors | length' 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo "    ✅ Wrong Content-Type properly rejected"
    else
        echo "    ❌ Wrong Content-Type should be rejected with errors"
        exit 1
    fi
else
    echo "    ❌ Content-Type validation test failed (Status: $WRONG_CONTENT_TYPE_STATUS)"
    echo "    Expected status 400, got $WRONG_CONTENT_TYPE_STATUS"
    exit 1
fi

# 空のリクエストボディテスト
echo "  Testing empty request body..."
EMPTY_BODY_RESPONSE=$(curl -s -w "%{http_code}" \
  -H "Content-Type: application/json" \
  -d '' \
  -o /tmp/empty_body_response.json \
  "$GRAPHQL_ENDPOINT")

EMPTY_BODY_STATUS=${EMPTY_BODY_RESPONSE: -3}

if [ "$EMPTY_BODY_STATUS" = "400" ]; then
    ERROR_COUNT=$(cat /tmp/empty_body_response.json | jq '.errors | length' 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo "    ✅ Empty body properly rejected"
    else
        echo "    ❌ Empty body should be rejected with errors"
        exit 1
    fi
else
    echo "    ❌ Empty body test failed (Status: $EMPTY_BODY_STATUS)"
    echo "    Expected status 400, got $EMPTY_BODY_STATUS"
    exit 1
fi

# 無効なJSONテスト
echo "  Testing invalid JSON..."
INVALID_JSON_RESPONSE=$(curl -s -w "%{http_code}" \
  -H "Content-Type: application/json" \
  -d '{"query": invalid json}' \
  -o /tmp/invalid_json_response.json \
  "$GRAPHQL_ENDPOINT")

INVALID_JSON_STATUS=${INVALID_JSON_RESPONSE: -3}

if [ "$INVALID_JSON_STATUS" = "400" ]; then
    ERROR_COUNT=$(cat /tmp/invalid_json_response.json | jq '.errors | length' 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo "    ✅ Invalid JSON properly rejected"
    else
        echo "    ❌ Invalid JSON should be rejected"
        exit 1
    fi
else
    echo "    ❌ Invalid JSON test failed (Status: $INVALID_JSON_STATUS)"
    exit 1
fi

# 無効なクエリ
echo "  Testing invalid GraphQL query..."
INVALID_QUERY='{"query": "invalid graphql query"}'

INVALID_RESPONSE=$(curl -s -w "%{http_code}" \
  -H "Content-Type: application/json" \
  -d "$INVALID_QUERY" \
  -o /tmp/invalid_response.json \
  "$GRAPHQL_ENDPOINT")

INVALID_STATUS_CODE=${INVALID_RESPONSE: -3}

if [ "$INVALID_STATUS_CODE" = "400" ]; then
    ERROR_COUNT=$(cat /tmp/invalid_response.json | jq '.errors | length' 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo "    ✅ Invalid GraphQL query properly rejected with errors"
    else
        echo "    ❌ Invalid GraphQL query should have errors"
        exit 1
    fi
else
    echo "    ❌ Invalid GraphQL query test failed (Status: $INVALID_STATUS_CODE)"
    exit 1
fi

# 空のクエリテスト
echo "  Testing empty query..."
EMPTY_QUERY='{"query": ""}'

EMPTY_QUERY_RESPONSE=$(curl -s -w "%{http_code}" \
  -H "Content-Type: application/json" \
  -d "$EMPTY_QUERY" \
  -o /tmp/empty_query_response.json \
  "$GRAPHQL_ENDPOINT")

EMPTY_QUERY_STATUS=${EMPTY_QUERY_RESPONSE: -3}

if [ "$EMPTY_QUERY_STATUS" = "400" ]; then
    ERROR_COUNT=$(cat /tmp/empty_query_response.json | jq '.errors | length' 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo "    ✅ Empty query properly rejected"
    else
        echo "    ❌ Empty query should be rejected"
        exit 1
    fi
else
    echo "    ❌ Empty query test failed (Status: $EMPTY_QUERY_STATUS)"
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

if [ "$MISSING_STATUS_CODE" = "400" ]; then
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

# CORS テスト
echo "  Testing CORS headers..."
CORS_RESPONSE=$(curl -s -I -H "Origin: http://localhost:3000" -H "Content-Type: application/json" -X OPTIONS "$GRAPHQL_ENDPOINT")

if echo "$CORS_RESPONSE" | grep -q "Access-Control-Allow-Origin: http://localhost:3000"; then
    echo "    ✅ CORS headers properly configured for allowed origin"
else
    echo "    ❌ CORS headers not properly configured"
    echo "$CORS_RESPONSE"
    exit 1
fi

# 不正なOriginテスト
echo "  Testing unauthorized origin..."
UNAUTHORIZED_ORIGIN_RESPONSE=$(curl -s -I -H "Origin: http://malicious-site.com" -H "Content-Type: application/json" -X OPTIONS "$GRAPHQL_ENDPOINT")

if echo "$UNAUTHORIZED_ORIGIN_RESPONSE" | grep -q "Access-Control-Allow-Origin: http://malicious-site.com"; then
    echo "    ❌ Unauthorized origin should be rejected"
    exit 1
else
    echo "    ✅ Unauthorized origin properly rejected"
fi

echo

# クリーンアップ
echo "Cleaning up temporary files..."
rm -f /tmp/health_response.json
rm -f /tmp/graphql_response.json
rm -f /tmp/multi_response_*.json
rm -f /tmp/wrong_content_type_response.json
rm -f /tmp/empty_body_response.json
rm -f /tmp/invalid_json_response.json
rm -f /tmp/invalid_response.json
rm -f /tmp/empty_query_response.json
rm -f /tmp/missing_response.json

echo
echo "=== $TEST_NAME COMPLETED SUCCESSFULLY ==="
echo "✅ All tests passed!"