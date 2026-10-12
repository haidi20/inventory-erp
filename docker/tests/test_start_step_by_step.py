import os
import shutil
import subprocess
import tempfile
import unittest


class TestStartStepByStepBat(unittest.TestCase):
    BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    SCRIPT = os.path.join(BASE_DIR, "start_step_by_step.bat")

    def setUp(self):
        self.temp_dir = tempfile.mkdtemp(prefix="step-by-step-")
        self.log_file = os.path.join(self.temp_dir, "docker.log")
        self._write_fake_docker()
        self.env = os.environ.copy()
        self.env["PATH"] = self.temp_dir + os.pathsep + self.env["PATH"]
        self.env["RUN_BAT_TEST_LOG"] = self.log_file
        self.env["RUN_BAT_TEST_FORCE_WINDOWS"] = "1"
        self.env["RUN_BAT_TEST_SKIP_HTTP_CHECK"] = "1"

    def tearDown(self):
        shutil.rmtree(self.temp_dir, ignore_errors=True)

    def _write_fake_docker(self):
        docker_cmd = os.path.join(self.temp_dir, "docker.cmd")
        with open(docker_cmd, "w", encoding="ascii", newline="\r\n") as fh:
            fh.write("@echo off\r\n")
            fh.write(">> \"%RUN_BAT_TEST_LOG%\" echo docker %*\r\n")
            fh.write("exit /b 0\r\n")

    def run_script(self, *args):
        return subprocess.run(
            ["cmd", "/d", "/c", self.SCRIPT, *args],
            cwd=self.BASE_DIR,
            capture_output=True,
            text=True,
            env=self.env,
            timeout=20,
        )

    def read_log(self):
        if not os.path.exists(self.log_file):
            return ""
        with open(self.log_file, "r", encoding="utf-8", errors="ignore") as fh:
            return fh.read()

    def assert_log_order(self, ordered_lines):
        content = self.read_log()
        cursor = -1
        for line in ordered_lines:
            next_pos = content.find(line, cursor + 1)
            self.assertNotEqual(next_pos, -1, f"log line not found: {line}")
            self.assertGreater(next_pos, cursor, f"log line out of order: {line}")
            cursor = next_pos

    def test_stage_2_runs_infra_boot_sequence(self):
        result = self.run_script("2")
        self.assertEqual(result.returncode, 0, result.stderr or result.stdout)
        content = self.read_log()
        self.assertIn("compose up -d postgres_dev redis_dev postgres_prod redis_prod pgadmin", content)

    def test_stage_9_checks_dev_api_health_url(self):
        result = self.run_script("9")
        self.assertEqual(result.returncode, 0, result.stderr or result.stdout)
        content = self.read_log()
        self.assertIn("shell curl -i http://127.0.0.1:8088/dev/api/health", content)

    def test_stage_10_checks_cicd_login_url(self):
        result = self.run_script("10")
        self.assertEqual(result.returncode, 0, result.stderr or result.stdout)
        content = self.read_log()
        self.assertIn("shell curl -i http://127.0.0.1:8088/cicd/login", content)

    def test_stage_12_runs_all_steps_in_visible_order(self):
        result = self.run_script("12")
        self.assertEqual(result.returncode, 0, result.stderr or result.stdout)
        self.assert_log_order(
            [
                "compose down --remove-orphans",
                "compose up -d postgres_dev redis_dev postgres_prod redis_prod pgadmin",
                "compose ps",
                "compose up -d jenkins",
                "compose up -d minio netdata",
                "compose up -d be_dev be_prod fe_dev fe_prod nginx",
                'compose exec -T fe_dev sh -lc "node ./scripts/docker_publish_frontend.js dev /publish/dev"',
                'compose exec -T fe_prod sh -lc "node ./scripts/docker_publish_frontend.js prod /publish/prod"',
                "shell curl -i http://127.0.0.1:8088/dev/api/health",
                "shell curl -i http://127.0.0.1:8088/cicd/login",
                "shell curl -i http://127.0.0.1:8088/prod/api/health",
            ]
        )


if __name__ == "__main__":
    unittest.main()
