#!/usr/bin/env python3
"""Send a chat completion request to apigod.cloud â€” dengan auto-ping."""

import json
import os
import sys
import ssl
import urllib.error
import urllib.request
import time
import argparse
from datetime import datetime

API_URL = "https://apigod.cloud/v1/chat/completions"
MODEL = "gpt-5.5"
API_KEY = "sk-0bf68de6f39f9dd25445bcb976610ea9105bb0346a6f0660"


def send_request(api_key: str, prompt: str, timeout: int = 60) -> dict | None:
    """Kirim satu request ke API, return response atau None jika gagal."""
    payload = {
        "model": MODEL,
        "messages": [{"role": "user", "content": prompt}],
    }

    request = urllib.request.Request(
        API_URL,
        data=json.dumps(payload).encode("utf-8"),
        headers={
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json",
        },
        method="POST",
    )

    try:
        with urllib.request.urlopen(request, timeout=timeout, context=ssl._create_unverified_context()) as response:
            body = response.read().decode("utf-8", errors="replace")
            if body.lstrip().startswith("data:"):
                content_parts = []
                last_payload = None
                for line in body.splitlines():
                    line = line.strip()
                    if not line.startswith("data:"):
                        continue
                    data = line.removeprefix("data:").strip()
                    if not data or data == "[DONE]":
                        continue
                    try:
                        chunk = json.loads(data)
                    except json.JSONDecodeError:
                        continue
                    last_payload = chunk
                    for choice in chunk.get("choices", []):
                        delta = choice.get("delta", {})
                        content_parts.append(delta.get("content", ""))
                return {
                    "choices": [{"message": {"content": "".join(content_parts)}}],
                    "raw": last_payload,
                }

            try:
                return json.loads(body)
            except json.JSONDecodeError as error:
                return {"error": f"Invalid JSON response: {error}", "body": body[:1000]}
    except urllib.error.HTTPError as error:
        return {"error": f"HTTP {error.code}: {error.reason}", "body": error.read().decode("utf-8", errors="replace")}
    except urllib.error.URLError as error:
        return {"error": f"Request failed: {error.reason}"}
    except Exception as error:
        return {"error": str(error)}


def ping_mode(api_key: str, interval: int = 1, max_count: int | None = None):
    """Loop ping setiap interval detik. Hanya tampilkan jika GAGAL."""
    ts_start = datetime.now().strftime("%H:%M:%S")
    print(f"[{ts_start}] Ping mode: {interval}s interval | Ctrl+C berhenti")

    request_count = 0
    error_count = 0
    while True:
        request_count += 1
        ts = datetime.now().strftime("%H:%M:%S")
        prompt = "oke"

        try:
            result = send_request(api_key, prompt, timeout=30)
            if result and "error" not in result:
                pass  # Sukses - diam saja
            else:
                error_count += 1
                err = result.get("error", "tidak ada response") if result else "tidak ada response"
                print(f"[{ts}] FAIL #{request_count} ({error_count}) - {err}")
        except KeyboardInterrupt:
            print(f"\nDihentikan. Total: {request_count} req, {error_count} gagal.")
            break
        except Exception as error:
            error_count += 1
            print(f"[{ts}] FAIL #{request_count} ({error_count}) - {error}")

        if max_count is not None and request_count >= max_count:
            print(f"\nSelesai. Total: {request_count} req, {error_count} gagal.")
            break

        time.sleep(interval)


def single_mode(api_key: str, prompt: str):
    """Kirim satu request, tampilkan response lengkap."""
    result = send_request(api_key, prompt)
    if result and "error" not in result:
        choice = result.get("choices", [{}])[0]
        content = choice.get("message", {}).get("content", "")
        print(content)
        return 0
    else:
        err = result.get("error", "Unknown error") if result else "No response"
        print(f"Gagal: {err}", file=sys.stderr)
        if "body" in result:
            print(result["body"], file=sys.stderr)
        return 1


def main() -> int:
    parser = argparse.ArgumentParser(description="apigod.chat CLI â€” ping mode (default 1 detik)")
    parser.add_argument("prompt", nargs="*", help="Prompt untuk --single mode")
    parser.add_argument("-s", "--single", action="store_true", help="Satu request saja")
    parser.add_argument("-i", "--interval", type=int, default=1, help="Interval detik (default: 1)")
    parser.add_argument("-c", "--count", type=int, default=None, help="Jumlah ping (default: unlimited)")
    args = parser.parse_args()

    api_key = os.getenv("APIGOD_API_KEY") or API_KEY
    if not api_key:
        print("Set APIGOD_API_KEY dulu:", file=sys.stderr)
        print("  PowerShell: $env:APIGOD_API_KEY='kunci-api'", file=sys.stderr)
        return 1

    if args.single:
        prompt = " ".join(args.prompt).strip() or "Halo"
        return single_mode(api_key, prompt)

    ping_mode(api_key, interval=args.interval, max_count=args.count)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
