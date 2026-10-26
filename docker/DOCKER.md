# Docker & CI/CD Documentation

This document explains the containerization and automation setup for the monorepo, focusing on the Backend (`be`) and Frontend (`fe`) services.

## 🐳 Docker Setup

The project uses Docker for environment consistency and simplified deployment.

### Prerequisites

- [Docker Engine](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Local Development

1.  **Environment Setup**:
    Copy the example environment files (if not already present):
    ```bash
    cp be/.env.example be/.env
    cp fe/.env.dev fe/.env
    ```

2.  **Start Services**:
    Run all services in detached mode:
    ```bash
    docker compose up -d
    ```

3.  **Hot Reloading (Optional)**:
    Create a `docker-compose.override.yml` based on the example to enable volume mounting and faster builds:
    ```bash
    cp docker-compose.override.yml.example docker-compose.override.yml
    docker compose up -d
    ```

### Service Information

| Service | Internal Port | External Port | Purpose |
| :--- | :--- | :--- | :--- |
| `mysql` | 3306 | 3306 | Database |
| `redis` | 6379 | 6379 | Cache/Queue |
| `be` | 8001 | 8001 | Go Backend API |
| `fe` | 3000 | 3000 | Vite Frontend (Dev) |

---

## 🛠 CI/CD Pipeline

The project uses GitHub Actions for continuous integration and delivery.

### Workflow: Monorepo CI (`ci.yml`)

Triggered on every push to `main`, `master`, `dev`, and all Pull Requests.

- **Backend Tests**: Runs Go unit tests and package-level tests.
- **Frontend Tests**: Validates Node.js dependencies and runs Vitest suites.
- **Docker Build Check**: Verifies that both Backend and Frontend Dockerfiles can build successfully using the `prod` target.

### Workflow: Docker Publish (`docker-publish.yml`)

Triggered on tag pushes (e.g., `v1.0.0`) and merges to `main`.

- Builds production-ready images.
- Publishes images to [GitHub Container Registry (GHCR)](https://github.com/features/packages).
- Automates version tagging based on Git tags/shas.

---

## 🔒 Security & Best Practices

- **Non-Root Execution**: Both production images (`be` and `fe`) run as a non-privileged `klinik` user.
- **Multi-Stage Builds**: Final images contain only production artifacts, minimizing the attack surface.
- **Healthchecks**: Services include container-native healthchecks for robust orchestration.

### PHP Version Audit

| Environment | PHP version | Source |
| --- | --- | --- |
| Docker app (dev/prod) | 8.3.36 (pinned digest) | `docker/app/Dockerfile` |
