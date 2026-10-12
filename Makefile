.PHONY: docker-up docker-down docker-logs docker-ps
.PHONY: fe-install fe-dev fe-type-check fe-test
.PHONY: be-install be-compile
.PHONY: dev-be dev-fe dev-all

# Monorepo root helpers untuk backend (be) dan frontend (fe).

docker-up:
	docker compose up -d --build

docker-down:
	docker compose down

docker-logs:
	docker compose logs -f --tail=100

docker-ps:
	docker compose ps

fe-install:
	cd fe && npm ci

fe-dev:
	cd fe && npm run dev

fe-type-check:
	cd fe && npm run type-check

fe-test:
	cd fe && npm run test -- --run

be-install:
	cd be && npm ci

be-compile:
	cd be && go test ./app/... ./routes/... -run '^$$'

dev-be:
	powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\dev-be.ps1

dev-fe:
	powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\dev-fe.ps1

dev-all:
	powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\dev-all.ps1
