import os
import subprocess
import time
import unittest

import requests


RUN_DOCKER_INTEGRATION = os.getenv("RUN_DOCKER_INTEGRATION") == "1"


@unittest.skipUnless(
    RUN_DOCKER_INTEGRATION,
    "Docker integration stability test hanya dijalankan jika RUN_DOCKER_INTEGRATION=1",
)
class TestGatewayStability(unittest.TestCase):
    BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    RUN_BAT = os.path.join(BASE_DIR, "run.bat")
    CHECKPOINTS = [0, 5, 10, 15, 20]
    TIMEOUT_SECONDS = 10
    BOOTSTRAP_TIMEOUT_SECONDS = 600

    URLS = {
        "fe_dev": {
            "url": "http://127.0.0.1:8088/dev/",
            "allowed_statuses": {200},
            "body_must_contain": [b"<html", b"<!doctype html"],
        },
        "fe_prod": {
            "url": "http://127.0.0.1:8088/prod/",
            "allowed_statuses": {200},
            "body_must_contain": [b"<html", b"<!doctype html"],
        },
        "api_dev_health": {
            "url": "http://127.0.0.1:8088/dev/api/health",
            "allowed_statuses": {200},
            "body_must_contain": [b'"status":"ok"', b'"service":"be"'],
        },
        "api_prod_health": {
            "url": "http://127.0.0.1:8088/prod/api/health",
            "allowed_statuses": {200},
            "body_must_contain": [b'"status":"ok"', b'"service":"be"'],
        },
        "cicd": {
            "url": "http://127.0.0.1:8088/cicd/",
            "allowed_statuses": {200, 301, 302, 303, 307, 308, 403},
            "forbidden_redirect_fragments": ["/dev/login", "/prod/login"],
        },
        "legacy_cicd_dev_redirect": {
            "url": "http://127.0.0.1:8088/dev/cicd/",
            "allowed_statuses": {301},
            "required_redirect_fragment": "/cicd/",
        },
        "legacy_cicd_prod_redirect": {
            "url": "http://127.0.0.1:8088/prod/cicd/",
            "allowed_statuses": {301},
            "required_redirect_fragment": "/cicd/",
        },
        "netdata": {
            "url": "http://127.0.0.1:19999/",
            "allowed_statuses": {200},
            "body_must_contain": [b"netdata", b"dashboard", b"monitoring"],
        },
    }

    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls._bootstrap_stack()
        cls._wait_for_gateway_ready()

    def test_gateway_urls_are_stable_across_checkpoints(self):
        started = time.monotonic()

        for checkpoint in self.CHECKPOINTS:
            self._wait_until_checkpoint(started, checkpoint)
            for name, config in self.URLS.items():
                with self.subTest(checkpoint_seconds=checkpoint, endpoint=name):
                    response = requests.get(
                        config["url"],
                        timeout=self.TIMEOUT_SECONDS,
                        allow_redirects=False,
                    )

                    self.assertIn(
                        response.status_code,
                        config["allowed_statuses"],
                        f"{name} di checkpoint {checkpoint}s mengembalikan status {response.status_code}",
                    )

                    if "body_must_contain" in config:
                        lowered_body = response.content.lower()
                        self.assertTrue(
                            any(expected in lowered_body for expected in config["body_must_contain"]),
                            f"{name} di checkpoint {checkpoint}s tidak mengandung payload yang diharapkan",
                        )

                    if "forbidden_redirect_fragments" in config:
                        location = response.headers.get("Location", "")
                        for forbidden in config["forbidden_redirect_fragments"]:
                            self.assertNotIn(
                                forbidden,
                                location,
                                f"{name} di checkpoint {checkpoint}s masih mengarah ke route FE login: {location}",
                            )

                    if "required_redirect_fragment" in config:
                        location = response.headers.get("Location", "")
                        self.assertIn(
                            config["required_redirect_fragment"],
                            location,
                            f"{name} di checkpoint {checkpoint}s tidak mengarah ke CI/CD tunggal: {location}",
                        )

    def _wait_until_checkpoint(self, started: float, checkpoint: int) -> None:
        target = started + checkpoint
        remaining = target - time.monotonic()
        if remaining > 0:
            time.sleep(remaining)

    @classmethod
    def _bootstrap_stack(cls) -> None:
        result = subprocess.run(
            ["cmd", "/d", "/c", cls.RUN_BAT, "--worker", "1", "postgres"],
            cwd=cls.BASE_DIR,
            capture_output=True,
            text=True,
            timeout=cls.BOOTSTRAP_TIMEOUT_SECONDS,
        )
        if result.returncode != 0:
            raise AssertionError(
                "Bootstrap stack Docker gagal.\n"
                f"STDOUT:\n{result.stdout}\n\nSTDERR:\n{result.stderr}"
            )

    @classmethod
    def _wait_for_gateway_ready(cls) -> None:
        deadline = time.monotonic() + cls.BOOTSTRAP_TIMEOUT_SECONDS
        required_urls = [
            cls.URLS["fe_dev"]["url"],
            cls.URLS["fe_prod"]["url"],
            cls.URLS["api_dev_health"]["url"],
            cls.URLS["api_prod_health"]["url"],
            cls.URLS["cicd"]["url"],
            cls.URLS["legacy_cicd_dev_redirect"]["url"],
            cls.URLS["legacy_cicd_prod_redirect"]["url"],
            cls.URLS["netdata"]["url"],
        ]

        last_errors = []
        while time.monotonic() < deadline:
            last_errors = []
            for url in required_urls:
                try:
                    response = requests.get(url, timeout=cls.TIMEOUT_SECONDS, allow_redirects=False)
                    if response.status_code not in {200, 301, 302, 303, 307, 308, 403}:
                        last_errors.append(f"{url} -> status {response.status_code}")
                except requests.RequestException as exc:
                    last_errors.append(f"{url} -> {exc}")
            if not last_errors:
                return
            time.sleep(5)

        raise AssertionError(
            "Gateway Docker lokal tidak siap sebelum stability test dimulai.\n"
            + "\n".join(last_errors)
        )


if __name__ == "__main__":
    unittest.main()
