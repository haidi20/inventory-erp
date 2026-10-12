@echo off
title Clean Reinstall Codex
echo ===================================================
echo [1/4] Menghentikan proses Codex yang berjalan...
echo ===================================================
taskkill /F /IM codex.exe /T 2>nul
taskkill /F /IM node.exe /FI "WINDOWTITLE eq codex*" 2>nul
taskkill /F /IM python.exe /FI "WINDOWTITLE eq codex*" 2>nul

echo.
echo ===================================================
echo [2/4] Mencopot pemasangan (Uninstall) Codex...
echo ===================================================
:: Tambahkan "call" agar script tidak berhenti di sini
call pip uninstall -y codex 2>nul
call npm uninstall -g codex 2>nul

echo.
echo ===================================================
echo [3/4] Membersihkan sisa file cache dan konfigurasi...
echo ===================================================
echo [*] Membersihkan folder AppData...
IF EXIST "%APPDATA%\codex" rd /s /q "%APPDATA%\codex"
IF EXIST "%APPDATA%\Codex" rd /s /q "%APPDATA%\Codex"
IF EXIST "%LOCALAPPDATA%\codex" rd /s /q "%LOCALAPPDATA%\codex"
IF EXIST "%LOCALAPPDATA%\Codex" rd /s /q "%LOCALAPPDATA%\Codex"

echo [*] Membersihkan konfigurasi di User Profile...
IF EXIST "%USERPROFILE%\.codex" rd /s /q "%USERPROFILE%\.codex"

echo [*] Membersihkan sisa file di direktori saat ini...
IF EXIST "config.toml" del /q "config.toml" 2>nul
IF EXIST ".cache" rd /s /q ".cache" 2>nul

echo.
echo ===================================================
echo STATUS: BERSIH! Sisa file lama telah dimusnahkan.
echo ===================================================
echo.
echo ===================================================
echo [4/4] Memulai proses Install Ulang...
echo ===================================================

:: Tambahkan "call" untuk eksekusi instalasi
call pip install codex

echo.
echo ===================================================
echo SELESAI! Codex berhasil diinstal ulang dengan bersih.
echo ===================================================
pause
