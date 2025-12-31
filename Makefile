.PHONY: dev-up dev-down dev-build dev-rebuild test coverage lint fmt phpstan cs shell

.PHONY: help
help: ## このヘルプを表示
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

.PHONY: docker-compose-up
docker-compose-up:
	docker compose \
		-f tools/docker-compose/docker-compose-databases.yml \
		-f tools/docker-compose/docker-compose-applications.yml \
		up -d

.PHONY: docker-compose-build
docker-compose-build:
	docker compose \
		-f tools/docker-compose/docker-compose-databases.yml \
		-f tools/docker-compose/docker-compose-applications.yml \
		build

.PHONY: docker-compose-down
docker-compose-down:
	docker compose \
		-f tools/docker-compose/docker-compose-databases.yml \
		-f tools/docker-compose/docker-compose-applications.yml \
		down

.PHONY: verify-group-chat
verify-group-chat:
	./tools/e2e-test/verify-group-chat.sh

test: ## テストを実行
	docker compose \
		-f tools/docker-compose/docker-compose-databases.yml \
		-f tools/docker-compose/docker-compose-applications.yml \
		exec app composer test

coverage: ## カバレッジ付きテストを実行
	docker compose \
		-f tools/docker-compose/docker-compose-databases.yml \
		-f tools/docker-compose/docker-compose-applications.yml \
		exec app composer test:coverage

lint: ## リント（フォーマットチェック + 静的解析）を実行
	docker compose \
		-f tools/docker-compose/docker-compose-databases.yml \
		-f tools/docker-compose/docker-compose-applications.yml \
		exec app composer lint

fmt: ## コードフォーマットを実行
	docker compose \
		-f tools/docker-compose/docker-compose-databases.yml \
		-f tools/docker-compose/docker-compose-applications.yml \
		exec app composer fmt

phpstan: ## PHPStanで静的解析を実行
	docker compose \
		-f tools/docker-compose/docker-compose-databases.yml \
		-f tools/docker-compose/docker-compose-applications.yml \
		exec app composer phpstan

cs: ## コードスタイルをチェック（dry-run）
	docker compose \
		-f tools/docker-compose/docker-compose-databases.yml \
		-f tools/docker-compose/docker-compose-applications.yml \
		exec app composer cs
