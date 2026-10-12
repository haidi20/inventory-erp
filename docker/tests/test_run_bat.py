import subprocess
import unittest
import requests
import time
import os
import json

RUN_DOCKER_INTEGRATION = os.getenv("RUN_DOCKER_INTEGRATION") == "1"


@unittest.skipUnless(
    RUN_DOCKER_INTEGRATION,
    "Docker integration test hanya dijalankan jika RUN_DOCKER_INTEGRATION=1",
)
class TestDockerRunBat(unittest.TestCase):
    BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    RUN_BAT = os.path.join(BASE_DIR, "run.bat")

    # Important project URLs from run.bat
    URLS = {
        "FE_DEV": "http://127.0.0.1:8088/dev/",
        "FE_PROD": "http://127.0.0.1:8088/prod/",
        "API_DEV_HEALTH": "http://127.0.0.1:8088/dev/api/health",
        "API_PROD_HEALTH": "http://127.0.0.1:8088/prod/api/health",
        "JENKINS": "http://127.0.0.1:8088/cicd/login",
        "NETDATA": "http://127.0.0.1:19999/",
        "PGADMIN": "http://127.0.0.1:5050/",
        "MINIO_CONSOLE": "http://localhost:9001/"
    }

    def run_command(self, option):
        """Executes run.bat with the given option."""
        print(f"\n[Testing] Executing: run.bat {option}")
        result = subprocess.run(
            f"cmd /c run.bat {option}",
            cwd=self.BASE_DIR,
            shell=True,
            capture_output=True,
            text=True
        )
        return result

    def wait_for_url(self, url, timeout=60):
        """Wait for a URL to return a 200 OK status."""
        start_time = time.time()
        while time.time() - start_time < timeout:
            try:
                response = requests.get(url, timeout=5)
                if response.status_code == 200:
                    print(f"  [OK] {url} is accessible.")
                    return True
            except requests.exceptions.RequestException:
                pass
            time.sleep(2)
        print(f"  [FAIL] {url} is not accessible after {timeout} seconds.")
        return False

    def get_runner(self):
        """Detects if docker should be run natively or via WSL."""
        if subprocess.run("where docker", shell=True, capture_output=True).returncode == 0:
            return "windows"
        if subprocess.run("where wsl", shell=True, capture_output=True).returncode == 0:
            return "wsl"
        return "none"

    def get_running_containers(self):
        """Returns a list of running container names using docker compose ps."""
        runner = self.get_runner()
        docker_cmd = "docker compose ps --format json"
        
        if runner == "wsl":
            # Translate Windows path (D:\...) to WSL path (/mnt/d/...)
            drive = self.BASE_DIR[0].lower()
            path = self.BASE_DIR[2:].replace("\\", "/")
            wsl_dir = f"/mnt/{drive}{path}"
            cmd = f"wsl -e sh -lc \"cd '{wsl_dir}' && {docker_cmd}\""
        else:
            cmd = docker_cmd

        print(f"  [Debug] Running status check: {cmd}")
        result = subprocess.run(
            cmd,
            cwd=self.BASE_DIR,
            shell=True,
            capture_output=True,
            text=True
        )
        
        if result.returncode != 0:
            print(f"  [Error] Status check failed: {result.stderr}")
            return []
        
        try:
            output = result.stdout.strip()
            if not output: return []
            
            containers = []
            for line in output.splitlines():
                if line.strip().startswith('{'):
                    try:
                        containers.append(json.loads(line))
                    except json.JSONDecodeError:
                        continue
            
            # Docker Compose status can be "running", "Up", or "healthy"
            running_states = ['running', 'up', 'healthy']
            found = []
            for c in containers:
                # Try multiple possible keys for status and service name
                state = str(c.get('State', '')).lower()
                status = str(c.get('Status', '')).lower()
                
                # Check if it's running
                is_running = any(s in state or s in status for s in running_states)
                
                if is_running:
                    # Identify the service name
                    service_name = c.get('Service') or c.get('Labels', {}).get('com.docker.compose.service')
                    if not service_name and c.get('Names'):
                        # If Names is a list, pick the first one; if string, use it
                        name = c.get('Names')
                        if isinstance(name, list) and name:
                            service_name = name[0]
                        else:
                            service_name = str(name)
                    
                    if service_name:
                        # Clean up names like 'klinik_be_dev' to just 'be_dev' if necessary
                        # or just keep it as is. run.bat expects 'be_dev'.
                        found.append(service_name)
                        # Also add normalized names (without prefix)
                        if '_' in service_name:
                            found.append(service_name.split('_', 1)[-1])
            return found
        except Exception as e:
            print(f"  [Error] Parsing docker compose output: {e}\nOutput: {result.stdout}")
            return []

    def test_option_11_stop_local(self):
        """Verify that 'run.bat 11' stops the stack."""
        result = self.run_command("11")
        self.assertEqual(result.returncode, 0, "run.bat 11 failed to execute")
        
        running = self.get_running_containers()
        self.assertTrue(len(running) == 0 or 'nginx' not in running, "Stack services are still running after stop")

    def test_option_1_run_all(self):
        """Verify that 'run.bat 1' starts all services."""
        result = self.run_command("1")
        self.assertEqual(result.returncode, 0, "run.bat 1 failed to execute")
        
        # Give services a moment to start
        time.sleep(5)
        
        running = self.get_running_containers()
        required_services = ['nginx', 'postgres_dev', 'redis_dev', 'fe_dev', 'be_dev']
        for service in required_services:
            self.assertIn(service, running, f"Service {service} is not running")

    def test_option_2_fe_dev_only(self):
        """Verify that 'run.bat 2' refreshes FE/Nginx."""
        result = self.run_command("2")
        self.assertEqual(result.returncode, 0, "run.bat 2 failed to execute")
        
        self.assertTrue(self.wait_for_url(self.URLS["FE_DEV"]), "FE Dev URL is not accessible")

    def test_option_3_be_dev_only(self):
        """Verify that 'run.bat 3' refreshes Backend Dev."""
        result = self.run_command("3")
        self.assertEqual(result.returncode, 0, "run.bat 3 failed to execute")
        
        self.assertTrue(self.wait_for_url(self.URLS["API_DEV_HEALTH"]), "API Dev Health URL is not accessible")

    def test_urls_full_sweep(self):
        """Verify accessibility of all auxiliary services."""
        print("\n[Testing] Sweeping all project URLs...")
        # Jenkins/PgAdmin/MinIO might take longer to boot
        for name, url in self.URLS.items():
            with self.subTest(url=name):
                self.assertTrue(self.wait_for_url(url, timeout=30), f"{name} ({url}) is down")

if __name__ == "__main__":
    unittest.main()
