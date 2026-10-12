import os
import unittest


class TestWindowsPortBridge(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        bridge_path = os.path.join(cls.base_dir, "windows_port_bridge.py")
        with open(bridge_path, "r", encoding="utf-8") as fh:
            cls.bridge = fh.read()

    def test_bridge_defaults_cover_required_ports(self):
        self.assertIn("DEFAULT_PORTS = [8088, 8081, 19999", self.bridge)
        self.assertIn("15432", self.bridge)
        self.assertIn("15433", self.bridge)
        self.assertIn("13306", self.bridge)
        self.assertIn("13307", self.bridge)
        self.assertIn('parser.add_argument("--wsl-ip", default=None', self.bridge)

    def test_bridge_persists_pid_and_supports_stop(self):
        self.assertIn('PID_FILE = os.path.join(RUNTIME_DIR, "windows_port_bridge.pid")', self.bridge)
        self.assertIn("def stop_existing_bridge()", self.bridge)
        self.assertIn('subprocess.run(["taskkill", "/PID", str(pid), "/T", "/F"]', self.bridge)
        self.assertIn('parser.add_argument("--stop", action="store_true"', self.bridge)

    def test_bridge_prefers_private_wsl_ips(self):
        self.assertIn('if candidate.startswith(("172.", "10.", "192.168."))', self.bridge)
        self.assertIn('WSL_EXE = os.path.join(os.environ.get("WINDIR", r"C:\\Windows"), "System32", "wsl.exe")', self.bridge)
        self.assertIn('[WSL_EXE, "-e", "sh", "-lc", "hostname -I"]', self.bridge)

    def test_bridge_skips_conflicting_ports_instead_of_crashing_entire_bridge(self):
        self.assertIn("skipped_ports = []", self.bridge)
        self.assertIn("except OSError as exc:", self.bridge)
        self.assertIn('raise RuntimeError("Tidak ada port bridge yang berhasil dibuka di Windows host.")', self.bridge)


if __name__ == "__main__":
    unittest.main()
