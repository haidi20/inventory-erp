import os
import shutil
import subprocess
import tempfile
import time
import unittest


class TestRunBatBackground(unittest.TestCase):
    BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    RUN_BAT = os.path.join(BASE_DIR, "run.bat")

    def setUp(self):
        self.temp_dir = tempfile.mkdtemp(prefix="run-bat-bg-")
        self.log_file = os.path.join(self.temp_dir, "docker.log")
        self._write_fake_docker()
        self.env = os.environ.copy()
        self.env["PATH"] = self.temp_dir + os.pathsep + self.env["PATH"]
        self.env["RUN_BAT_TEST_LOG"] = self.log_file
        self.env["RUN_BAT_TEST_FORCE_WINDOWS"] = "1"
        self.env["RUN_BAT_TEST_SKIP_HTTP_WAIT"] = "1"
        self.env["RUN_BAT_TEST_SKIP_PORTPROXY"] = "1"

    def tearDown(self):
        shutil.rmtree(self.temp_dir, ignore_errors=True)

    def _write_fake_docker(self):
        docker_cmd = os.path.join(self.temp_dir, "docker.cmd")
        with open(docker_cmd, "w", encoding="ascii", newline="\r\n") as fh:
            fh.write("@echo off\r\n")
            fh.write("ping -n 4 127.0.0.1 >nul\r\n")
            fh.write(">> \"%RUN_BAT_TEST_LOG%\" echo %*\r\n")
            fh.write("exit /b 0\r\n")

    def run_batch(self, *args, timeout=10):
        started = time.time()
        result = subprocess.run(
            ["cmd", "/d", "/c", self.RUN_BAT, *args],
            cwd=self.BASE_DIR,
            capture_output=True,
            text=True,
            env=self.env,
            timeout=timeout,
        )
        elapsed = time.time() - started
        return result, elapsed

    def wait_for_log_contains(self, expected_lines, timeout=8):
        started = time.time()
        while time.time() - started < timeout:
            if os.path.exists(self.log_file):
                with open(self.log_file, "r", encoding="utf-8", errors="ignore") as fh:
                    content = fh.read()
                if all(line in content for line in expected_lines):
                    return True
            time.sleep(0.25)
        return False

    def assert_background_launch(self, args, expected_lines, max_seconds=2.5):
        result, elapsed = self.run_batch(*args)
        self.assertEqual(result.returncode, 0, result.stderr or result.stdout)
        self.assertLess(
            elapsed,
            max_seconds,
            f"run.bat should return quickly for background mode, got {elapsed:.2f}s",
        )
        self.assertTrue(
            self.wait_for_log_contains(expected_lines),
            f"background docker commands were not observed: {expected_lines}",
        )

    def assert_worker_commands(self, args, expected_lines, forbidden_lines=None, max_seconds=8, run_timeout=10):
        result, _ = self.run_batch(*args, timeout=run_timeout)
        self.assertEqual(result.returncode, 0, result.stderr or result.stdout)
        self.assertTrue(
            self.wait_for_log_contains(expected_lines, timeout=max_seconds),
            f"worker docker commands were not observed: {expected_lines}",
        )
        if forbidden_lines:
            with open(self.log_file, "r", encoding="utf-8", errors="ignore") as fh:
                content = fh.read()
            for forbidden in forbidden_lines:
                self.assertNotIn(forbidden, content, f"forbidden docker command found: {forbidden}")

    def assert_log_order(self, ordered_lines):
        with open(self.log_file, "r", encoding="utf-8", errors="ignore") as fh:
            content = fh.read()
        cursor = -1
        for line in ordered_lines:
            next_pos = content.find(line, cursor + 1)
            self.assertNotEqual(next_pos, -1, f"log line not found for ordering check: {line}")
            self.assertGreater(next_pos, cursor, f"log line out of order: {line}")
            cursor = next_pos

    def test_option_1_runs_in_background(self):
        result, elapsed = self.run_batch("1")
        self.assertEqual(result.returncode, 0, result.stderr or result.stdout)
        self.assertLess(
            elapsed,
            2.5,
            f"run.bat should return quickly for option 1, got {elapsed:.2f}s",
        )
        self.assertIn("Menjalankan semua container di background", result.stdout)

    def test_worker_option_1_starts_infra_then_apps_and_publishes_frontends(self):
        self.assert_worker_commands(
            ["--worker", "1"],
            [
                "WAIT_GATEWAY dev http://127.0.0.1:8088/dev/api/health",
                "WAIT_GATEWAY prod http://127.0.0.1:8088/prod/api/health",
                "WAIT_WEB cicd http://127.0.0.1:8088/cicd/login",
                "LABEL publish_frontend fe_dev dev /publish/dev",
                "LABEL publish_frontend fe_prod prod /publish/prod",
            ],
            forbidden_lines=[
                "compose stop",
                "compose restart nginx",
                "npm run preview",
            ],
            max_seconds=90,
            run_timeout=120,
        )

    def test_option_2_runs_in_background(self):
        self.assert_background_launch(
            ["2"],
            [
                "LAUNCH --worker 2",
            ],
        )

    def test_option_3_runs_in_background(self):
        self.assert_background_launch(
            ["3"],
            [
                "LAUNCH --worker 3",
            ],
        )

    def test_option_4_runs_in_background(self):
        self.assert_background_launch(
            ["4"],
            [
                "LAUNCH --worker 4",
            ],
        )

    def test_option_5_runs_in_background(self):
        self.assert_background_launch(
            ["5"],
            [
                "LAUNCH --worker 5",
            ],
        )

    def test_option_6_runs_in_background(self):
        self.assert_background_launch(
            ["6", "fe_dev"],
            [
                "LAUNCH --worker 6 fe_dev",
            ],
        )

    def test_worker_option_2_keeps_nginx_running_without_force_recreate(self):
        self.assert_worker_commands(
            ["--worker", "2"],
            [
                "compose up -d --wait postgres_dev redis_dev be_dev",
                "LABEL publish_frontend fe_dev dev /publish/dev",
            ],
            forbidden_lines=[
                "force-recreate --no-deps fe_dev",
                "compose restart nginx",
                "npm run build:dev",
                "docker_publish_frontend.sh",
            ],
            max_seconds=12,
            run_timeout=15,
        )
        self.assert_log_order(
            [
                "compose up -d --wait postgres_dev redis_dev be_dev",
                "LABEL publish_frontend fe_dev dev /publish/dev",
            ]
        )

    def test_worker_option_3_restarts_only_backend_dev_after_dependencies_are_ready(self):
        self.assert_worker_commands(
            ["--worker", "3"],
            [
                "compose up -d postgres_dev redis_dev nginx",
                "compose up -d --wait postgres_dev redis_dev",
                "compose up -d --force-recreate --no-deps be_dev",
                "WAIT_GATEWAY dev http://127.0.0.1:8088/dev/api/health",
            ],
            forbidden_lines=[
                "compose restart be_dev",
                "compose restart nginx",
            ],
            max_seconds=12,
            run_timeout=18,
        )
        self.assert_log_order(
            [
                "compose up -d postgres_dev redis_dev nginx",
                "compose up -d --wait postgres_dev redis_dev",
                "compose up -d --force-recreate --no-deps be_dev",
                "WAIT_GATEWAY dev http://127.0.0.1:8088/dev/api/health",
            ]
        )

    def test_worker_option_4_publishes_prod_without_nginx_restart(self):
        self.assert_worker_commands(
            ["--worker", "4"],
            [
                "compose up -d postgres_prod redis_prod be_prod fe_prod nginx",
                "compose up -d --wait postgres_prod redis_prod be_prod",
                "LABEL publish_frontend fe_prod prod /publish/prod",
            ],
            forbidden_lines=[
                "force-recreate --no-deps fe_prod",
                "compose restart nginx",
                "compose restart fe_prod",
                "npm run build:prod",
                "docker_publish_frontend.sh",
            ],
        )
        self.assert_log_order(
            [
                "compose up -d postgres_prod redis_prod be_prod fe_prod nginx",
                "compose up -d --wait postgres_prod redis_prod be_prod",
                "LABEL publish_frontend fe_prod prod /publish/prod",
            ]
        )

    def test_worker_option_5_waits_for_backend_prod_health(self):
        self.assert_worker_commands(
            ["--worker", "5"],
            [
                "compose up -d postgres_prod redis_prod nginx",
                "compose up -d --wait postgres_prod redis_prod",
                "compose up -d --force-recreate --no-deps be_prod",
                "WAIT_GATEWAY prod http://127.0.0.1:8088/prod/api/health",
            ],
            forbidden_lines=[
                "compose restart be_prod",
                "compose restart nginx",
            ],
            max_seconds=12,
            run_timeout=18,
        )

    def test_worker_option_6_for_jenkins_starts_gateway_and_waits_until_login_is_ready(self):
        self.assert_worker_commands(
            ["--worker", "6", "jenkins"],
            [
                "compose up -d nginx jenkins",
                "WAIT_WEB jenkins-direct http://127.0.0.1:8081/cicd/login",
                "WAIT_WEB cicd http://127.0.0.1:8088/cicd/login",
            ],
            forbidden_lines=[
                "compose restart jenkins",
                "compose restart nginx",
                "compose up -d --wait nginx",
            ],
            max_seconds=12,
            run_timeout=18,
        )
        self.assert_log_order(
            [
                "compose up -d nginx jenkins",
                "WAIT_WEB jenkins-direct http://127.0.0.1:8081/cicd/login",
                "WAIT_WEB cicd http://127.0.0.1:8088/cicd/login",
            ]
        )

    def test_worker_option_3_fails_when_gateway_wait_never_becomes_ready(self):
        env = self.env.copy()
        env["RUN_BAT_TEST_FAIL_GATEWAY_WAIT"] = "1"
        result = subprocess.run(
            ["cmd", "/d", "/c", self.RUN_BAT, "--worker", "3"],
            cwd=self.BASE_DIR,
            capture_output=True,
            text=True,
            env=env,
            timeout=15,
        )
        self.assertNotEqual(result.returncode, 0, "worker option 3 should fail when gateway wait fails")
        self.assertIn("Gagal: Menunggu gateway API dev siap", result.stdout)
        self.assertNotIn("Akses:", result.stdout, "worker failure should stop before printing success access hints")

    def test_option_9_uses_read_only_docker_ps_without_compose_side_effects(self):
        result, _ = self.run_batch("9", timeout=10)
        self.assertEqual(result.returncode, 0, result.stderr or result.stdout)
        self.assertTrue(
            self.wait_for_log_contains(["ps -a"]),
            "opsi 9 harus menjalankan docker ps read-only",
        )
        with open(self.log_file, "r", encoding="utf-8", errors="ignore") as fh:
            content = fh.read()
        self.assertNotIn("compose ps", content)
        self.assertNotIn("compose up", content)
        self.assertNotIn("compose down", content)
        self.assertNotIn("compose restart", content)


if __name__ == "__main__":
    unittest.main()
