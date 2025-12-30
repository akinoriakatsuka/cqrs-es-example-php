FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY patches ./patches/
RUN composer install --no-dev --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data /var/www/html

# CI/CD用のセットアップスクリプトを実行可能にする
RUN chmod +x scripts/init-dynamodb.php

# CI環境でのDynamoDBテーブル初期化
# 実際のCI環境では環境変数でDynamoDB接続情報を設定
RUN if [ "$CI" = "true" ]; then \
    echo "CI環境でのDynamoDBセットアップをスキップ（実行時に手動実行）"; \
    fi