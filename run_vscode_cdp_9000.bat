@echo off
setlocal

set "CDP_PORT=9000"
set "ANTIGRAVITY_EXE="
set "WORKSPACE_FILE=D:\projects\klinik-starter\klinik-starter.code-workspace"

echo.
echo ===================================================
echo   Mengaktifkan Antigravity CDP (Port %CDP_PORT%)
echo ===================================================
echo.

:: Cek dan tutup proses yang sudah ada
tasklist /FI "IMAGENAME eq Antigravity.exe" | find /I "Antigravity.exe" >nul
if not errorlevel 1 (
    echo Menghentikan proses Antigravity yang sedang berjalan...
    taskkill /F /T /IM Antigravity.exe >nul 2>&1
    echo Menunggu proses benar-benar berhenti...
    timeout /t 2 /nobreak >nul
)

:: Cari lokasi executable
for %%I in (
    "E:\apps\Antigravity\Antigravity.exe"
    "%LOCALAPPDATA%\Programs\Antigravity\Antigravity.exe"
    "%ProgramFiles%\Antigravity\Antigravity.exe"
    "%ProgramFiles(x86)%\Antigravity\Antigravity.exe"
) do (
    if exist %%~I (
        set "ANTIGRAVITY_EXE=%%~I"
        goto :run
    )
)

echo [ERROR] Antigravity.exe tidak ditemukan!
echo Silakan edit file ini dan sesuaikan path-nya.
pause
exit /b 1

:run
echo Memulai Antigravity dengan CDP pada port %CDP_PORT%...
if exist "%WORKSPACE_FILE%" (
    echo Membuka workspace: %WORKSPACE_FILE%
    start "" "%ANTIGRAVITY_EXE%" --remote-debugging-port=%CDP_PORT% "%WORKSPACE_FILE%"
) else (
    start "" "%ANTIGRAVITY_EXE%" --remote-debugging-port=%CDP_PORT%
)

echo.
echo Berhasil! Silakan cek panel Auto Accept setelah Antigravity terbuka.
echo.
timeout /t 3
exit /b 0
