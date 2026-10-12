# Jenkins Auto Trigger (Git Push)

## Ringkas
- Job Jenkins dibuat otomatis dari `docker/jenkins/init.groovy.d/02-seed-jobs.groovy`.
- Job yang dibuat:
  - `app-dev-delivery` (branch `dev`)
  - `app-prod-delivery` (branch `main`)
- Trigger memakai endpoint Generic Webhook:
  - `/cicd/generic-webhook-trigger/invoke?token=app-dev-delivery`
  - `/cicd/generic-webhook-trigger/invoke?token=app-prod-delivery`

## Aktivasi Hook Lokal
Agar `git push` langsung memicu CI/CD dari mesin lokal:

```sh
sh .githooks/setup-hooks.sh
```

Hook `post-push` akan mengirim webhook ke Jenkins (`http://127.0.0.1:8081/cicd`).

## Env Opsional
- `JENKINS_DEV_WEBHOOK_TOKEN`
- `JENKINS_PROD_WEBHOOK_TOKEN`
- `JENKINS_DEV_BRANCH`
- `JENKINS_PROD_BRANCH`
- `JENKINS_BASE_URL` (untuk hook lokal)
