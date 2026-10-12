import os
import unittest


class TestCICDTopology(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        with open(os.path.join(base_dir, "docker-compose.yml"), "r", encoding="utf-8") as fh:
            cls.compose = fh.read()
        with open(os.path.join(base_dir, "nginx", "dev-prod.conf"), "r", encoding="utf-8") as fh:
            cls.nginx = fh.read()
        with open(os.path.join(base_dir, "run.bat"), "r", encoding="utf-8", errors="ignore") as fh:
            cls.run_bat = fh.read()
        jenkins_dir = os.path.join(base_dir, "jenkins")
        with open(os.path.join(jenkins_dir, "Dockerfile"), "r", encoding="utf-8") as fh:
            cls.jenkins_dockerfile = fh.read()
        with open(os.path.join(jenkins_dir, "plugins.txt"), "r", encoding="utf-8") as fh:
            cls.jenkins_plugins = fh.read()
        with open(os.path.join(jenkins_dir, "init.groovy.d", "01-security.groovy"), "r", encoding="utf-8") as fh:
            cls.jenkins_security = fh.read()
        with open(os.path.join(jenkins_dir, "init.groovy.d", "02-seed-jobs.groovy"), "r", encoding="utf-8") as fh:
            cls.jenkins_jobs = fh.read()

    def test_compose_separates_dev_and_prod_databases_and_cicd(self):
        self.assertIn("postgres_dev:", self.compose)
        self.assertIn("postgres_prod:", self.compose)
        self.assertIn("mysql_dev:", self.compose)
        self.assertIn("mysql_prod:", self.compose)
        self.assertIn("redis_dev:", self.compose)
        self.assertIn("redis_prod:", self.compose)
        self.assertIn("jenkins:", self.compose)
        self.assertIn("netdata:", self.compose)
        self.assertNotIn("jenkins_dev:", self.compose)
        self.assertNotIn("jenkins_prod:", self.compose)

        self.assertIn("DB_HOST: ${BE_DEV_DB_HOST:-172.22.0.60}", self.compose)
        self.assertIn("DB_HOST: ${BE_PROD_DB_HOST:-172.22.0.61}", self.compose)
        self.assertIn("REDIS_HOST: 172.22.0.50", self.compose)
        self.assertIn("REDIS_HOST: 172.22.0.51", self.compose)
        self.assertIn("./runtime/local/jenkins:/var/jenkins_home", self.compose)

    def test_postgres_uses_pgdata_subdirectory_inside_host_mount(self):
        self.assertIn("PGDATA: /var/lib/postgresql/data/pgdata", self.compose)
        self.assertEqual(
            self.compose.count("PGDATA: /var/lib/postgresql/data/pgdata"),
            2,
            "dev dan prod harus sama-sama memakai subdirectory PGDATA agar tidak mentok di root data volume",
        )
        self.assertNotIn("PGDATA: /var/lib/postgresql/data\n", self.compose)

    def test_default_host_ports_avoid_laragon_conflicts(self):
        self.assertIn('${POSTGRES_DEV_PORT:-15432}:5432', self.compose)
        self.assertIn('${POSTGRES_PROD_PORT:-15433}:5432', self.compose)
        self.assertIn('${MYSQL_DEV_PORT:-13306}:3306', self.compose)
        self.assertIn('${MYSQL_PROD_PORT:-13307}:3306', self.compose)
        self.assertIn('${NGINX_HTTP_PORT:-8088}:80', self.compose)

    def test_postgres_uses_named_volumes_for_persistence_and_avoids_host_permission_issues(self):
        self.assertIn("postgres_dev_data:/var/lib/postgresql/data", self.compose)
        self.assertIn("postgres_prod_data:/var/lib/postgresql/data", self.compose)
        self.assertIn("postgres_dev_data:", self.compose)
        self.assertIn("postgres_prod_data:", self.compose)
        self.assertNotIn("./runtime/local/postgres_dev:/var/lib/postgresql/data", self.compose)
        self.assertNotIn("./runtime/local/postgres_prod:/var/lib/postgresql/data", self.compose)

    def test_compose_bootstraps_admin_password_and_jobs(self):
        self.assertIn("JENKINS_ADMIN_PASSWORD: ${JENKINS_ADMIN_PASSWORD:-qwerty123}", self.compose)
        self.assertIn("JENKINS_OPTS: --prefix=/cicd", self.compose)
        self.assertNotIn("JENKINS_ENVIRONMENT:", self.compose)
        self.assertIn('image: netdata/netdata:stable', self.compose)
        self.assertIn('${NETDATA_PORT:-19999}:19999', self.compose)
        self.assertIn('curl -fsS http://127.0.0.1:19999/api/v1/info >/dev/null || exit 1', self.compose)
        self.assertIn("build:", self.compose)
        self.assertIn("context: ./jenkins", self.compose)
        self.assertIn("dockerfile: Dockerfile", self.compose)

    def test_nginx_routes_each_cicd_environment_independently(self):
        self.assertIn("location = /cicd {", self.nginx)
        self.assertIn("return 301 /cicd/;", self.nginx)
        self.assertIn("location = /cicd/ {", self.nginx)
        self.assertIn("location ^~ /cicd/", self.nginx)
        self.assertIn("proxy_pass http://172.22.0.40:8080;", self.nginx)
        self.assertIn("location @cicd_unavailable {", self.nginx)
        self.assertIn("CI/CD belum siap", self.nginx)
        self.assertIn("location = /dev/cicd {", self.nginx)
        self.assertIn("return 301 /cicd/;", self.nginx)
        self.assertIn("location = /prod/cicd {", self.nginx)
        self.assertIn("return 301 /cicd/;", self.nginx)
        self.assertNotIn("/dev/login?redirect=/dev/cicd", self.nginx)
        self.assertNotIn("/prod/login?redirect=/prod/cicd", self.nginx)
        self.assertNotIn("proxy_pass http://172.22.0.40:8080/cicd/;", self.nginx)
        self.assertLess(
            self.nginx.find("location ^~ /cicd/"),
            self.nginx.find("location ^~ /dev/ {\n        rewrite ^/dev/?(.*)$ /$1 break;"),
        )
        self.assertLess(
            self.nginx.find("location = /prod/cicd {"),
            self.nginx.find("location ^~ /prod/ {\n        rewrite ^/prod/?(.*)$ /$1 break;"),
        )

    def test_nginx_cicd_routes_have_controlled_fallback_instead_of_falling_to_vue(self):
        self.assertIn("error_page 404 502 503 504 = @cicd_unavailable;", self.nginx)
        self.assertIn("proxy_intercept_errors on;", self.nginx)

    def test_nginx_api_has_controlled_fallback_instead_of_raw_502(self):
        self.assertIn("location = /dev/api/health {", self.nginx)
        self.assertIn("proxy_pass http://172.22.0.30:8001/health;", self.nginx)
        self.assertIn("proxy_intercept_errors on;", self.nginx)
        self.assertIn("error_page 502 503 504 = @dev_api_unavailable;", self.nginx)
        self.assertIn("location @dev_api_unavailable {", self.nginx)
        self.assertIn('"environment":"dev"', self.nginx)
        self.assertIn('"status":"starting"', self.nginx)
        self.assertLess(
            self.nginx.find("location = /dev/api/health {"),
            self.nginx.find("location ^~ /dev/api/ {"),
        )
        self.assertIn("location = /prod/api/health {", self.nginx)
        self.assertIn("proxy_pass http://172.22.0.31:8001/health;", self.nginx)
        self.assertIn("error_page 502 503 504 = @prod_api_unavailable;", self.nginx)
        self.assertIn("location @prod_api_unavailable {", self.nginx)
        self.assertIn('"environment":"prod"', self.nginx)
        self.assertLess(
            self.nginx.find("location = /prod/api/health {"),
            self.nginx.find("location ^~ /prod/api/ {"),
        )

    def test_run_bat_uses_dev_and_prod_specific_infra(self):
        run_local_start = self.run_bat.find(":run_local")
        run_local_minimal_start = self.run_bat.find(":run_local_minimal")
        run_local_section = self.run_bat[run_local_start:run_local_minimal_start]

        self.assertIn('set "DB_SERVICES=postgres_dev postgres_prod pgadmin"', self.run_bat)
        self.assertIn('set "DB_SERVICES=mysql_dev mysql_prod phpmyadmin"', self.run_bat)
        self.assertIn("compose_local up -d --force-recreate redis_dev redis_prod !DB_SERVICES! jenkins", self.run_bat)
        self.assertIn("compose_local up -d --wait redis_dev redis_prod !DB_SERVICES!", self.run_bat)
        self.assertIn("compose_local up -d --force-recreate netdata", self.run_bat)
        self.assertIn("compose_local build jenkins be_dev be_prod", self.run_bat)
        self.assertIn("be_dev be_prod fe_dev fe_prod nginx", self.run_bat)
        self.assertIn("compose_local up -d postgres_dev redis_dev be_dev fe_dev nginx", self.run_bat)
        self.assertIn("compose_local up -d postgres_prod redis_prod be_prod fe_prod nginx", self.run_bat)
        self.assertIn("compose_local down --remove-orphans", run_local_section)
        self.assertNotIn("compose_local stop", run_local_section)
        self.assertIn("compose_local up -d --wait postgres_dev redis_dev be_dev", self.run_bat)
        self.assertIn("compose_local up -d --wait postgres_prod redis_prod be_prod", self.run_bat)
        self.assertIn("compose_local up -d --wait jenkins", self.run_bat)
        self.assertIn("call :sync_windows_wsl_host_ports", self.run_bat)
        self.assertIn('set "PORTPROXY_SYNC_SCRIPT=%DOCKER_DIR_WIN%\\sync_windows_portproxy.ps1"', self.run_bat)
        self.assertIn('set "WINDOWS_PORT_BRIDGE_SCRIPT=%DOCKER_DIR_WIN%\\windows_port_bridge.py"', self.run_bat)
        self.assertIn('set "WSL_KEEPALIVE_PID=%DOCKER_DIR_WIN%\\runtime\\local\\wsl_keepalive.pid"', self.run_bat)
        self.assertIn('call :wait_gateway_health "dev" "%LOCAL_API_DEV_HEALTH_URL%" 30', self.run_bat)
        self.assertNotIn("compose_local restart be_dev", self.run_bat)
        self.assertNotIn("compose_local restart be_prod", self.run_bat)
        self.assertIn('call :wait_gateway_health "prod" "%LOCAL_API_PROD_HEALTH_URL%" 30', self.run_bat)
        self.assertIn('call :wait_web_url "cicd" "%LOCAL_CICD_LOGIN_URL%" 40 "Jenkins"', self.run_bat)
        self.assertIn('set "LOCAL_CICD_URL=%LOCAL_GATEWAY_BASE%/cicd/"', self.run_bat)
        self.assertIn('set "LOCAL_NETDATA_URL=http://127.0.0.1:%NETDATA_PORT%/"', self.run_bat)
        self.assertIn(
            'powershell -NoProfile -ExecutionPolicy Bypass -Command "$cmd = \'cd ""%DOCKER_DIR_WSL%""; %FULL_COMPOSE_CMD%\'; & wsl -e sh -lc $cmd; exit $LASTEXITCODE"',
            self.run_bat,
        )
        self.assertIn(
            'powershell -NoProfile -ExecutionPolicy Bypass -Command "$cmd = \'cd ""%DOCKER_DIR_WSL%""; %FULL_DOCKER_CMD%\'; & wsl -e sh -lc $cmd; exit $LASTEXITCODE"',
            self.run_bat,
        )
        self.assertIn('echo [16] netdata', self.run_bat)
        self.assertIn('if /I "%~1"=="netdata" set "SELECTED_SERVICE=netdata"', self.run_bat)
        self.assertIn('set /p engine_choice="Pilih Database ([P]ostgres / [M]ysql) [Default P]: "', self.run_bat)
        self.assertIn('if not defined NGINX_HTTP_PORT set "NGINX_HTTP_PORT=8088"', self.run_bat)
        self.assertIn('if not defined NETDATA_PORT set "NETDATA_PORT=19999"', self.run_bat)
        self.assertIn(":sync_windows_wsl_host_ports", self.run_bat)
        self.assertIn('powershell -NoProfile -ExecutionPolicy Bypass -File "%PORTPROXY_SYNC_SCRIPT%"', self.run_bat)
        self.assertIn("if /I not \"%DOCKER_RUNNER%\"==\"wsl\" exit /b 0", self.run_bat)
        self.assertIn("PORTPROXY_SYNC", self.run_bat)
        self.assertIn(":launch_windows_host_bridge", self.run_bat)
        self.assertIn(":stop_windows_host_bridge", self.run_bat)
        self.assertIn("HOST_BRIDGE_LAUNCH", self.run_bat)
        self.assertIn("HOST_BRIDGE_STOP", self.run_bat)
        self.assertIn("Start-Process -FilePath 'python' -WindowStyle Hidden", self.run_bat)
        self.assertIn(":resolve_current_wsl_ip", self.run_bat)
        self.assertIn("'--wsl-ip','!CURRENT_WSL_IP!'", self.run_bat)
        self.assertIn('findstr /I /C:"networkingMode=mirrored" "%USERPROFILE%\\.wslconfig" >nul 2>&1', self.run_bat)
        self.assertIn(":ensure_wsl_keepalive", self.run_bat)
        self.assertIn(":stop_wsl_keepalive", self.run_bat)
        self.assertIn("WSL_KEEPALIVE_START", self.run_bat)
        self.assertIn("WSL_KEEPALIVE_STOP", self.run_bat)
        self.assertIn("sleep 2147483647", self.run_bat)

    def test_run_bat_has_jenkins_specific_flow_that_keeps_wsl_alive_and_waits_for_gateway(self):
        run_jenkins_start = self.run_bat.find(":run_start_jenkins_direct")
        start_service_menu = self.run_bat.find(":start_service_menu")
        self.assertNotEqual(run_jenkins_start, -1)
        self.assertNotEqual(start_service_menu, -1)
        jenkins_section = self.run_bat[run_jenkins_start:start_service_menu]

        self.assertIn('set "LOCAL_JENKINS_DIRECT_URL=http://127.0.0.1:%JENKINS_PORT%/cicd/login"', self.run_bat)
        self.assertIn('if /I "!SELECTED_SERVICE!"=="jenkins" goto run_start_jenkins_direct', self.run_bat)
        self.assertIn("call :compose_local up -d nginx jenkins", jenkins_section)
        self.assertIn("call :ensure_wsl_keepalive", jenkins_section)
        self.assertIn("call :sync_windows_wsl_host_ports", jenkins_section)
        self.assertIn('call :wait_web_url "jenkins-direct" "%LOCAL_JENKINS_DIRECT_URL%" 80 "Jenkins"', jenkins_section)
        self.assertIn('call :wait_web_url "cicd" "%LOCAL_CICD_LOGIN_URL%" 80 "Jenkins"', jenkins_section)
        self.assertIn("Jenkins direct : %LOCAL_JENKINS_DIRECT_URL%", jenkins_section)
        self.assertIn("CI/CD gateway  : %LOCAL_CICD_URL%", jenkins_section)
        self.assertNotIn("compose_local up -d --wait nginx", jenkins_section)
        self.assertNotIn("compose_local restart jenkins", jenkins_section)
        self.assertLess(
            jenkins_section.find("call :compose_local up -d nginx jenkins"),
            jenkins_section.find('call :wait_web_url "jenkins-direct" "%LOCAL_JENKINS_DIRECT_URL%" 80 "Jenkins"'),
        )
        self.assertLess(
            jenkins_section.find('call :wait_web_url "jenkins-direct" "%LOCAL_JENKINS_DIRECT_URL%" 80 "Jenkins"'),
            jenkins_section.find('call :wait_web_url "cicd" "%LOCAL_CICD_LOGIN_URL%" 80 "Jenkins"'),
        )

    def test_status_option_is_read_only_and_uses_direct_docker_ps(self):
        self.assertIn('if "%choice%"=="9" if defined BATCH_DIRECT (goto show_status_local) else goto status_menu', self.run_bat)
        self.assertIn(":show_status_local", self.run_bat)
        self.assertIn(':docker_local_ps', self.run_bat)
        self.assertIn('call :run_docker "ps -a"', self.run_bat)
        self.assertIn("call %FULL_DOCKER_CMD%", self.run_bat)
        self.assertIn("call %FULL_COMPOSE_CMD%", self.run_bat)
        show_status_start = self.run_bat.find(":show_status_local")
        print_urls_start = self.run_bat.find(":print_local_urls")
        show_status_section = self.run_bat[show_status_start:print_urls_start]
        self.assertNotIn("compose up", show_status_section)
        self.assertNotIn("compose down", show_status_section)
        self.assertNotIn("compose restart", show_status_section)

    def test_be_services_use_project_dev_image_and_go_run_runtime_for_code_refresh(self):
        self.maxDiff = None
        be_dev_start = self.compose.find("be_dev:")
        be_prod_start = self.compose.find("be_prod:")
        be_dev_section = self.compose[be_dev_start:be_prod_start]
        fe_dev_start = self.compose.find("fe_dev:")
        be_prod_section = self.compose[be_prod_start:fe_dev_start]

        self.assertIn("build:", be_dev_section)
        self.assertIn("context: ..", be_dev_section)
        self.assertIn("dockerfile: docker/be/Dockerfile", be_dev_section)
        self.assertIn("target: dev", be_dev_section)
        self.assertNotIn("./be/air.docker.toml:/app/.air.docker.toml:ro", be_dev_section)
        self.assertIn('command: ["/usr/local/go/bin/go", "run", "main.go"]', be_dev_section)
        self.assertIn("start_period: 90s", be_dev_section)
        self.assertIn("build:", be_prod_section)
        self.assertIn("target: dev", be_prod_section)
        self.assertNotIn("./be/air.docker.toml:/app/.air.docker.toml:ro", be_prod_section)
        self.assertIn('command: ["/usr/local/go/bin/go", "run", "main.go"]', be_prod_section)
        self.assertIn("start_period: 90s", be_prod_section)
        self.assertNotIn("/go/bin/air -c /app/.air.docker.toml", self.compose)

    def test_custom_jenkins_image_has_required_tools_and_plugins(self):
        self.assertIn("apt-get install -y", self.jenkins_dockerfile)
        self.assertIn("docker.io", self.jenkins_dockerfile)
        self.assertIn("git", self.jenkins_dockerfile)
        self.assertIn("curl", self.jenkins_dockerfile)
        self.assertIn("workflow-aggregator", self.jenkins_plugins)
        self.assertIn("configuration-as-code", self.jenkins_plugins)

    def test_jenkins_security_script_creates_admin_user(self):
        self.assertIn("qwerty123", self.jenkins_security)
        self.assertIn("JENKINS_ADMIN_USER", self.jenkins_security)
        self.assertIn("JENKINS_ADMIN_PASSWORD", self.jenkins_security)
        self.assertIn("FullControlOnceLoggedInAuthorizationStrategy", self.jenkins_security)
        self.assertIn("setInstallState", self.jenkins_security)

    def test_jenkins_seed_jobs_cover_frontend_and_backend_per_environment(self):
        self.assertIn("fe-dev-delivery", self.jenkins_jobs)
        self.assertIn("be-dev-delivery", self.jenkins_jobs)
        self.assertIn("fe-prod-delivery", self.jenkins_jobs)
        self.assertIn("be-prod-delivery", self.jenkins_jobs)
        self.assertNotIn("JENKINS_ENVIRONMENT", self.jenkins_jobs)
        self.assertIn("docker exec -w /app klinik_fe_dev", self.jenkins_jobs)
        self.assertIn("docker exec -w /app klinik_fe_prod", self.jenkins_jobs)
        self.assertIn("docker exec -w /app klinik_be_dev", self.jenkins_jobs)
        self.assertIn("docker exec -w /app klinik_be_prod", self.jenkins_jobs)


if __name__ == "__main__":
    unittest.main()
