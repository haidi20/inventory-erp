@echo off
setlocal EnableDelayedExpansion
cd /d "%~dp0"
set "SCRIPT_EXIT_CODE=0"

if not defined NGINX_HTTP_PORT set "NGINX_HTTP_PORT=8088"
set "LOCAL_GATEWAY_BASE=http://127.0.0.1:%NGINX_HTTP_PORT%"

set "DOCKER_DIR_WIN=%cd%"
call :to_wsl_path "%DOCKER_DIR_WIN%"
set "DOCKER_DIR_WSL=%WSL_PATH%"
set "DIRECT_MODE="

call :detect_docker_runner
if errorlevel 1 goto exit

if not "%~1"=="" (
    set "DIRECT_MODE=1"
    set "choice=%~1"
    goto dispatch_choice
)

:menu
cls
echo ================================================
echo     Klinik Starter Docker Step By Step Runner
echo ================================================
echo Folder kerja : %cd%
echo Runner Docker: %DOCKER_RUNNER%
echo.
echo [1] Reset stack lokal
echo [2] Jalankan DB, Redis, dan PgAdmin
echo [3] Cek status infra
echo [4] Jalankan Jenkins
echo [5] Jalankan MinIO dan Netdata
echo [6] Jalankan app services
echo [7] Publish FE dev
echo [8] Publish FE prod
echo [9] Cek API health dev
echo [10] Cek CI/CD
echo [11] Cek API health prod
echo [12] Jalankan semua tahap berurutan
echo [13] Keluar
echo ================================================
set /p choice="Pilih opsi [1-13]: "

:dispatch_choice
if "%choice%"=="1" goto stage_reset
if "%choice%"=="2" goto stage_infra
if "%choice%"=="3" goto stage_status
if "%choice%"=="4" goto stage_jenkins
if "%choice%"=="5" goto stage_minio
if "%choice%"=="6" goto stage_apps
if "%choice%"=="7" goto stage_publish_dev
if "%choice%"=="8" goto stage_publish_prod
if "%choice%"=="9" goto stage_check_api_dev
if "%choice%"=="10" goto stage_check_cicd_dev
if "%choice%"=="11" goto stage_check_api_prod
if "%choice%"=="12" goto stage_all
if "%choice%"=="13" goto exit

echo Pilihan tidak valid.
pause
goto menu

:stage_reset
echo.
echo [STEP 1] Reset stack lokal...
call :do_stage_reset
goto after_stage

:stage_infra
echo.
echo [STEP 2] Jalankan DB, Redis, dan PgAdmin...
call :do_stage_infra
goto after_stage

:stage_status
echo.
echo [STEP 3] Cek status infra...
call :do_stage_status
goto after_stage

:stage_jenkins
echo.
echo [STEP 4] Jalankan Jenkins...
call :do_stage_jenkins
goto after_stage

:stage_minio
echo.
echo [STEP 5] Jalankan MinIO dan Netdata...
call :do_stage_minio
goto after_stage

:stage_apps
echo.
echo [STEP 6] Jalankan app services...
call :do_stage_apps
goto after_stage

:stage_publish_dev
echo.
echo [STEP 7] Publish FE dev...
call :do_stage_publish_dev
goto after_stage

:stage_publish_prod
echo.
echo [STEP 8] Publish FE prod...
call :do_stage_publish_prod
goto after_stage

:stage_check_api_dev
echo.
echo [STEP 9] Cek API health dev...
call :do_stage_check_api_dev
goto after_stage

:stage_check_cicd_dev
echo.
echo [STEP 10] Cek CI/CD...
call :do_stage_check_cicd
goto after_stage

:stage_check_api_prod
echo.
echo [STEP 11] Cek API health prod...
call :do_stage_check_api_prod
goto after_stage

:stage_all
echo.
echo [ALL] Menjalankan semua tahap secara berurutan...
call :do_stage_reset
if errorlevel 1 goto exit
call :do_stage_infra
if errorlevel 1 goto exit
call :do_stage_status
if errorlevel 1 goto exit
call :do_stage_jenkins
if errorlevel 1 goto exit
call :do_stage_status
if errorlevel 1 goto exit
call :do_stage_minio
if errorlevel 1 goto exit
call :do_stage_apps
if errorlevel 1 goto exit
call :do_stage_publish_dev
if errorlevel 1 goto exit
call :do_stage_publish_prod
if errorlevel 1 goto exit
call :do_stage_check_api_dev
if errorlevel 1 goto exit
call :do_stage_check_cicd
if errorlevel 1 goto exit
call :do_stage_check_api_prod
if errorlevel 1 goto exit
goto after_stage

:do_stage_reset
call :compose_local down --remove-orphans
call :abort_if_failed "Reset stack lokal"
exit /b %ERRORLEVEL%

:do_stage_infra
call :compose_local up -d postgres_dev redis_dev postgres_prod redis_prod pgadmin
call :abort_if_failed "Menjalankan DB, Redis, dan PgAdmin"
exit /b %ERRORLEVEL%

:do_stage_status
call :compose_local ps
call :abort_if_failed "Cek status infra"
exit /b %ERRORLEVEL%

:do_stage_jenkins
call :compose_local up -d jenkins
call :abort_if_failed "Menjalankan Jenkins"
exit /b %ERRORLEVEL%

:do_stage_minio
call :compose_local up -d minio netdata
call :abort_if_failed "Menjalankan MinIO dan Netdata"
exit /b %ERRORLEVEL%

:do_stage_apps
call :compose_local up -d be_dev be_prod fe_dev fe_prod nginx
call :abort_if_failed "Menjalankan app services"
exit /b %ERRORLEVEL%

:do_stage_publish_dev
call :compose_local exec -T fe_dev sh -lc "node ./scripts/docker_publish_frontend.js dev /publish/dev"
call :abort_if_failed "Publish FE dev"
exit /b %ERRORLEVEL%

:do_stage_publish_prod
call :compose_local exec -T fe_prod sh -lc "node ./scripts/docker_publish_frontend.js prod /publish/prod"
call :abort_if_failed "Publish FE prod"
exit /b %ERRORLEVEL%

:do_stage_check_api_dev
call :run_shell "curl -i %LOCAL_GATEWAY_BASE%/dev/api/health"
call :abort_if_failed "Cek API health dev"
exit /b %ERRORLEVEL%

:do_stage_check_api_prod
call :run_shell "curl -i %LOCAL_GATEWAY_BASE%/prod/api/health"
call :abort_if_failed "Cek API health prod"
exit /b %ERRORLEVEL%

:do_stage_check_cicd
call :run_shell "curl -i %LOCAL_GATEWAY_BASE%/cicd/login"
call :abort_if_failed "Cek CI/CD"
exit /b %ERRORLEVEL%

:after_stage
if defined DIRECT_MODE goto exit
echo.
pause
goto menu

:detect_docker_runner
set "DOCKER_RUNNER="
if defined RUN_BAT_TEST_FORCE_WINDOWS (
    set "DOCKER_RUNNER=windows"
    exit /b 0
)
where docker >nul 2>&1
if not errorlevel 1 (
    set "DOCKER_RUNNER=windows"
    exit /b 0
)
where wsl >nul 2>&1
if not errorlevel 1 (
    set "DOCKER_RUNNER=wsl"
    exit /b 0
)
echo Docker CLI di Windows tidak ditemukan dan WSL juga tidak tersedia.
exit /b 1

:compose_local
set "COMPOSE_ARGS=%*"
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo compose %COMPOSE_ARGS%
)
call :run_compose "docker compose %COMPOSE_ARGS%"
exit /b %ERRORLEVEL%

:run_shell
set "SHELL_CMD=%~1"
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo shell %SHELL_CMD%
)
if defined RUN_BAT_TEST_SKIP_HTTP_CHECK exit /b 0
if /I "%DOCKER_RUNNER%"=="windows" (
    powershell -NoProfile -ExecutionPolicy Bypass -Command "%SHELL_CMD%"
    exit /b %ERRORLEVEL%
)
call :run_compose "%SHELL_CMD%"
exit /b %ERRORLEVEL%

:run_compose
set "FULL_COMPOSE_CMD=%~1"
if /I "%DOCKER_RUNNER%"=="windows" (
    %FULL_COMPOSE_CMD%
    exit /b %ERRORLEVEL%
)
wsl -e sh -lc "cd '%DOCKER_DIR_WSL%' && %FULL_COMPOSE_CMD%"
exit /b %ERRORLEVEL%

:abort_if_failed
if errorlevel 1 (
    echo.
    echo Gagal: %~1
    set "SCRIPT_EXIT_CODE=1"
    exit /b 1
)
exit /b 0

:to_wsl_path
set "WIN_PATH=%~1"
set "DRIVE_LETTER=%WIN_PATH:~0,1%"
set "PATH_NO_DRIVE=%WIN_PATH:~2%"
set "PATH_NO_DRIVE=%PATH_NO_DRIVE:\=/%"
set "WSL_PATH=/mnt/%DRIVE_LETTER%%PATH_NO_DRIVE%"
for %%a in (A B C D E F G H I J K L M N O P Q R S T U V W X Y Z) do (
    if /I "%DRIVE_LETTER%"=="%%a" set "WSL_PATH=/mnt/%%a%PATH_NO_DRIVE%"
)
set "WSL_PATH=%WSL_PATH:/mnt/A=/mnt/a%"
set "WSL_PATH=%WSL_PATH:/mnt/B=/mnt/b%"
set "WSL_PATH=%WSL_PATH:/mnt/C=/mnt/c%"
set "WSL_PATH=%WSL_PATH:/mnt/D=/mnt/d%"
set "WSL_PATH=%WSL_PATH:/mnt/E=/mnt/e%"
set "WSL_PATH=%WSL_PATH:/mnt/F=/mnt/f%"
set "WSL_PATH=%WSL_PATH:/mnt/G=/mnt/g%"
set "WSL_PATH=%WSL_PATH:/mnt/H=/mnt/h%"
set "WSL_PATH=%WSL_PATH:/mnt/I=/mnt/i%"
set "WSL_PATH=%WSL_PATH:/mnt/J=/mnt/j%"
set "WSL_PATH=%WSL_PATH:/mnt/K=/mnt/k%"
set "WSL_PATH=%WSL_PATH:/mnt/L=/mnt/l%"
set "WSL_PATH=%WSL_PATH:/mnt/M=/mnt/m%"
set "WSL_PATH=%WSL_PATH:/mnt/N=/mnt/n%"
set "WSL_PATH=%WSL_PATH:/mnt/O=/mnt/o%"
set "WSL_PATH=%WSL_PATH:/mnt/P=/mnt/p%"
set "WSL_PATH=%WSL_PATH:/mnt/Q=/mnt/q%"
set "WSL_PATH=%WSL_PATH:/mnt/R=/mnt/r%"
set "WSL_PATH=%WSL_PATH:/mnt/S=/mnt/s%"
set "WSL_PATH=%WSL_PATH:/mnt/T=/mnt/t%"
set "WSL_PATH=%WSL_PATH:/mnt/U=/mnt/u%"
set "WSL_PATH=%WSL_PATH:/mnt/V=/mnt/v%"
set "WSL_PATH=%WSL_PATH:/mnt/W=/mnt/w%"
set "WSL_PATH=%WSL_PATH:/mnt/X=/mnt/x%"
set "WSL_PATH=%WSL_PATH:/mnt/Y=/mnt/y%"
set "WSL_PATH=%WSL_PATH:/mnt/Z=/mnt/z%"
exit /b 0

:exit
endlocal & exit /b %SCRIPT_EXIT_CODE%
