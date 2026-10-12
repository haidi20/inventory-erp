import argparse
import atexit
import os
import re
import select
import signal
import socket
import socketserver
import subprocess
import sys
import threading
from typing import Iterable, List


DEFAULT_PORTS = [8088, 8081, 19999, 5050, 8083, 9000, 9001, 15432, 15433, 13306, 13307, 6379, 6380, 50010]
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
RUNTIME_DIR = os.path.join(BASE_DIR, "runtime", "local")
PID_FILE = os.path.join(RUNTIME_DIR, "windows_port_bridge.pid")
WSL_EXE = os.path.join(os.environ.get("WINDIR", r"C:\Windows"), "System32", "wsl.exe")


def extract_candidate_ips(hostname_output: str) -> List[str]:
    candidates = re.findall(r"\b\d{1,3}(?:\.\d{1,3}){3}\b", hostname_output or "")
    preferred = []
    fallback = []
    for candidate in candidates:
        if candidate.startswith(("172.", "10.", "192.168.")):
            preferred.append(candidate)
        else:
            fallback.append(candidate)
    return preferred + fallback


def get_wsl_ip() -> str:
    result = subprocess.run(
        [WSL_EXE, "-e", "sh", "-lc", "hostname -I"],
        capture_output=True,
        text=True,
        check=True,
    )
    candidates = extract_candidate_ips(result.stdout)
    if not candidates:
        raise RuntimeError("IP WSL tidak ditemukan dari output hostname -I")
    return candidates[0]


def ensure_runtime_dir() -> None:
    os.makedirs(RUNTIME_DIR, exist_ok=True)


def read_pid() -> int | None:
    try:
        with open(PID_FILE, "r", encoding="ascii") as fh:
            raw = fh.read().strip()
        return int(raw) if raw else None
    except (OSError, ValueError):
        return None


def write_pid() -> None:
    ensure_runtime_dir()
    with open(PID_FILE, "w", encoding="ascii") as fh:
        fh.write(str(os.getpid()))


def clear_pid() -> None:
    try:
        os.remove(PID_FILE)
    except FileNotFoundError:
        pass


def stop_existing_bridge() -> None:
    pid = read_pid()
    if not pid:
        clear_pid()
        return
    subprocess.run(["taskkill", "/PID", str(pid), "/T", "/F"], capture_output=True, text=True)
    clear_pid()


class RelayTCPServer(socketserver.ThreadingTCPServer):
    daemon_threads = True
    allow_reuse_address = True

    def __init__(self, server_address, target_host: str, target_port: int):
        self.target_host = target_host
        self.target_port = target_port
        super().__init__(server_address, RelayHandler)


class RelayHandler(socketserver.BaseRequestHandler):
    def handle(self) -> None:
        upstream = socket.create_connection((self.server.target_host, self.server.target_port), timeout=10)
        try:
            sockets = [self.request, upstream]
            while True:
                readable, _, exceptional = select.select(sockets, [], sockets, 30)
                if exceptional:
                    return
                if not readable:
                    continue
                for sock in readable:
                    chunk = sock.recv(65536)
                    if not chunk:
                        return
                    peer = upstream if sock is self.request else self.request
                    peer.sendall(chunk)
        finally:
            try:
                upstream.close()
            except OSError:
                pass


def start_bridge(listen_host: str, ports: Iterable[int], target_host: str | None = None) -> None:
    target_host = target_host or get_wsl_ip()
    ensure_runtime_dir()
    write_pid()
    atexit.register(clear_pid)

    servers = []
    threads = []
    bound_ports = []
    skipped_ports = []

    def shutdown(*_args) -> None:
        for server in servers:
            try:
                server.shutdown()
            except Exception:
                pass
            try:
                server.server_close()
            except Exception:
                pass
        clear_pid()
        sys.exit(0)

    signal.signal(signal.SIGTERM, shutdown)
    signal.signal(signal.SIGINT, shutdown)

    for port in ports:
        try:
            server = RelayTCPServer((listen_host, int(port)), target_host, int(port))
        except OSError as exc:
            skipped_ports.append(f"{port} ({exc})")
            continue

        thread = threading.Thread(target=server.serve_forever, daemon=True)
        thread.start()
        servers.append(server)
        threads.append(thread)
        bound_ports.append(str(port))

    if not servers:
        clear_pid()
        raise RuntimeError("Tidak ada port bridge yang berhasil dibuka di Windows host.")

    print(f"Windows bridge aktif di {listen_host} -> WSL {target_host} untuk port: {', '.join(bound_ports)}")
    if skipped_ports:
        print(f"Port bridge yang dilewati: {', '.join(skipped_ports)}")
    try:
        threading.Event().wait()
    finally:
        shutdown()


def build_arg_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(description="Bridge localhost Windows ke port Docker yang hidup di WSL.")
    parser.add_argument("--stop", action="store_true", help="Hentikan bridge yang sedang aktif jika ada.")
    parser.add_argument("--listen-host", default="127.0.0.1", help="Alamat listener Windows. Default: 127.0.0.1")
    parser.add_argument("--ports", nargs="*", type=int, default=DEFAULT_PORTS, help="Daftar port yang dijembatani.")
    parser.add_argument("--wsl-ip", default=None, help="Override IP WSL jika sudah diketahui.")
    return parser


def main() -> int:
    parser = build_arg_parser()
    args = parser.parse_args()

    if args.stop:
        stop_existing_bridge()
        return 0

    stop_existing_bridge()
    start_bridge(args.listen_host, args.ports, args.wsl_ip)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
