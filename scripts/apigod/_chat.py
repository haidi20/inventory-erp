#!/usr/bin/env python3
"""Compatibility entrypoint for scripts/apigod_chat.py."""

from pathlib import Path
import runpy

ROOT = Path(__file__).resolve().parents[1]
runpy.run_path(str(ROOT / "apigod_chat.py"), run_name="__main__")
