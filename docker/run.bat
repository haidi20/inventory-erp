@echo off
setlocal EnableDelayedExpansion
cd /d "%~dp0"
set "SCRIPT_EXIT_CODE=0"

if not defined NGINX_HTTP_PORT set "NGINX_HTTP_PORT=8088"
if not defined PGADMIN_PORT set "PGADMIN_PORT=5050"
if not defined PHPMYADMIN_PORT set "PHPMYADMIN_PORT=8083"
if not defined MINIO_API_PORT set "MINIO_API_PORT=9000"
if not defined MINIO_CONSOLE_PORT set "MINIO_CONSOLE_PORT=9001"
if not defined JENKINS_PORT set "JENKINS_PORT=8081"
if not defined NETDATA_PORT set "NETDATA_PORT=19999"

set "LOCAL_GATEWAY_BASE=http://127.0.0.1:%NGINX_HTTP_PORT%"
set "LOCAL_FE_DEV_URL=%LOCAL_GATEWAY_BASE%/dev/"
set "LOCAL_FE_PROD_URL=%LOCAL_GATEWAY_BASE%/prod/"
set "LOCAL_API_DEV_HEALTH_URL=%LOCAL_GATEWAY_BASE%/dev/api/health"
set "LOCAL_API_PROD_HEALTH_URL=%LOCAL_GATEWAY_BASE%/prod/api/health"
set "LOCAL_CICD_URL=%LOCAL_GATEWAY_BASE%/cicd/"
set "LOCAL_CICD_LOGIN_URL=%LOCAL_GATEWAY_BASE%/cicd/login"
set "LOCAL_PGADMIN_URL=http://127.0.0.1:%PGADMIN_PORT%/"
set "LOCAL_PHPMYADMIN_URL=http://127.0.0.1:%PHPMYADMIN_PORT%/"
set "LOCAL_MINIO_API_URL=http://127.0.0.1:%MINIO_API_PORT%/"
set "LOCAL_MINIO_CONSOLE_URL=http://127.0.0.1:%MINIO_CONSOLE_PORT%/"
set "LOCAL_JENKINS_DIRECT_URL=http://127.0.0.1:%JENKINS_PORT%/cicd/login"
set "LOCAL_NETDATA_URL=http://127.0.0.1:%NETDATA_PORT%/"

set "DOCKER_DIR_WIN=%cd%"
call :to_wsl_path "%DOCKER_DIR_WIN%"
set "DOCKER_DIR_WSL=%WSL_PATH%"
set "PORTPROXY_SYNC_SCRIPT=%DOCKER_DIR_WIN%\sync_windows_portproxy.ps1"
set "WINDOWS_PORT_BRIDGE_SCRIPT=%DOCKER_DIR_WIN%\windows_port_bridge.py"
set "WINDOWS_PORT_BRIDGE_PID=%DOCKER_DIR_WIN%\runtime\local\windows_port_bridge.pid"
set "WSL_KEEPALIVE_PID=%DOCKER_DIR_WIN%\runtime\local\wsl_keepalive.pid"
set "RUN_BAT_SELF=%~f0"
set "BATCH_DIRECT="
set "WORKER_MODE="
set "DIRECT_SERVICE="

if /I "%~1"=="--worker" (
    set "WORKER_MODE=1"
    set "BATCH_DIRECT=1"
    set "choice=%~2"
    if not "%~3"=="" set "DIRECT_SERVICE=%~3"
) else (
    if not "%~1"=="" set "BATCH_DIRECT=1"
    if defined BATCH_DIRECT set "choice=%~1"
    if not "%~2"=="" set "DIRECT_SERVICE=%~2"
)

call :detect_docker_runner
if errorlevel 1 goto exit
if defined BATCH_DIRECT goto dispatch_choice

:menu
cls
echo ================================================
echo            Klinik Starter Docker Manager
echo ================================================
echo Folder kerja : %cd%
echo Runner Docker: %DOCKER_RUNNER%
echo.
echo [1] Jalankan semua container
echo [2] Build dan publish FE dev
echo [3] Jalankan atau pastikan BE dev sehat
echo [4] Build dan publish FE prod
echo [5] Jalankan atau pastikan BE prod sehat
echo [6] Start service tertentu ^(stack lokal utama^)
echo [7] Stop service tertentu ^(stack lokal utama^)
echo [8] Recreate service tertentu ^(stack lokal utama^)
echo [9] Cek status container
echo [10] Lihat logs container
echo [11] Stop stack lokal utama
echo [12] Stop semua stack proyek
echo [13] Tampilkan URL akses penting
echo [14] Buka panduan docker_run.md
echo [15] Keluar
echo ================================================
set "choice="
set /p choice="Opsi [1-15]: "

:dispatch_choice
if not defined choice goto menu
if "%choice%"=="1" if defined WORKER_MODE (goto run_local) else goto queue_run_local
if "%choice%"=="2" if defined WORKER_MODE (goto run_local_minimal) else goto queue_run_local_minimal
if "%choice%"=="3" if defined WORKER_MODE (goto run_be_dev) else goto queue_run_be_dev
if "%choice%"=="4" if defined WORKER_MODE (goto run_fe_prod) else goto queue_run_fe_prod
if "%choice%"=="5" if defined WORKER_MODE (goto run_be_prod) else goto queue_run_be_prod
if "%choice%"=="6" if defined WORKER_MODE goto run_start_service_direct
if "%choice%"=="6" if defined DIRECT_SERVICE goto queue_start_service_direct
if "%choice%"=="6" goto start_service_menu
if "%choice%"=="7" goto stop_service_menu
if "%choice%"=="8" goto rebuild_service_menu
if "%choice%"=="9" if defined BATCH_DIRECT (goto show_status_local) else goto status_menu
if "%choice%"=="10" goto logs_menu
if "%choice%"=="11" goto stop_local
if "%choice%"=="12" goto stop_all
if "%choice%"=="13" goto show_urls
if "%choice%"=="14" goto open_run_guide
if "%choice%"=="15" goto exit

echo Pilihan tidak valid.
if defined BATCH_DIRECT goto exit
pause
goto menu

:queue_run_local
echo.
set "DB_ENGINE=postgres"
set /p engine_choice="Pilih Database ([P]ostgres / [M]ysql) [Default P]: "
if /I "%engine_choice%"=="M" set "DB_ENGINE=mysql"

if "%DB_ENGINE%"=="mysql" (
    set "DB_ENGINE_ARGS=mysql"
) else (
    set "DB_ENGINE_ARGS=postgres"
)

echo.
echo Menjalankan semua container di background (!DB_ENGINE!)...
call :launch_background "1" "!DB_ENGINE_ARGS!"
call :abort_and_exit_if_failed "Menjalankan semua container di background"
call :print_local_urls
goto exit

:queue_run_local_minimal
echo Menjalankan build dan publish FE dev di background...
call :launch_background "2"
call :abort_and_exit_if_failed "Menjalankan build dan publish FE dev di background"
echo FE dev akan dibuild dan dipublish di background.
goto exit

:queue_run_be_dev
echo Menjalankan BE dev di background...
call :launch_background "3"
call :abort_and_exit_if_failed "Menjalankan BE dev di background"
echo BE dev akan dijalankan di background.
goto exit

:queue_run_fe_prod
echo Menjalankan build dan publish FE prod di background...
call :launch_background "4"
call :abort_and_exit_if_failed "Menjalankan build dan publish FE prod di background"
echo FE prod akan dibuild dan dipublish di background.
goto exit

:queue_run_be_prod
echo Menjalankan BE prod di background...
call :launch_background "5"
call :abort_and_exit_if_failed "Menjalankan BE prod di background"
echo BE prod akan dijalankan di background.
goto exit

:queue_start_service_direct
call :resolve_local_service_token "%DIRECT_SERVICE%"
if not defined SELECTED_SERVICE (
    echo Service untuk opsi 6 tidak valid.
    goto exit
)
echo Menjalankan service !SELECTED_SERVICE! di background...
call :launch_background "6" "!SELECTED_SERVICE!"
call :abort_and_exit_if_failed "Menjalankan service !SELECTED_SERVICE! di background"
echo Service !SELECTED_SERVICE! akan dijalankan di background.
goto exit

:run_local
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo LABEL run_local
)
echo Menghentikan container proyek yang masih berjalan...
call :compose_local down --remove-orphans
call :abort_if_failed "Menghentikan container proyek yang masih berjalan dan membersihkan orphan"
if errorlevel 1 goto exit
echo.
set "DB_ENGINE=postgres"
if defined DIRECT_SERVICE (
    set "DB_ENGINE=%DIRECT_SERVICE%"
    echo Menggunakan Database Engine dari argumen: !DB_ENGINE!
) else if defined WORKER_MODE (
    set "DB_ENGINE=postgres"
    echo Menggunakan Database Engine default untuk worker: !DB_ENGINE!
) else (
    set /p engine_choice="Pilih Database ([P]ostgres / [M]ysql) [Default P]: "
    if /I "!engine_choice!"=="M" (set "DB_ENGINE=mysql") else (set "DB_ENGINE=postgres")
)

if "%DB_ENGINE%"=="mysql" (
    echo Database Engine: MySQL
    set "DB_SERVICES=mysql_dev mysql_prod phpmyadmin"
    set "BE_DEV_DB_DRIVER=mysql"
    set "BE_DEV_DB_HOST=172.22.0.62"
    set "BE_DEV_DB_PORT=3306"
    set "BE_DEV_DB_USER=root"
    set "BE_DEV_DB_PASSWORD=root"
    set "BE_DEV_DB_NAME=app_db_dev"
    set "BE_PROD_DB_DRIVER=mysql"
    set "BE_PROD_DB_HOST=172.22.0.63"
    set "BE_PROD_DB_PORT=3306"
    set "BE_PROD_DB_USER=root"
    set "BE_PROD_DB_PASSWORD=root"
    set "BE_PROD_DB_NAME=app_db_prod"
) else (
    echo Database Engine: Postgres
    set "DB_SERVICES=postgres_dev postgres_prod pgadmin"
    set "BE_DEV_DB_DRIVER=postgres"
    set "BE_DEV_DB_HOST=172.22.0.60"
    set "BE_DEV_DB_PORT=5432"
    set "BE_DEV_DB_USER=postgres"
    set "BE_DEV_DB_PASSWORD=postgres"
    set "BE_DEV_DB_NAME=app_db_dev"
    set "BE_PROD_DB_DRIVER=postgres"
    set "BE_PROD_DB_HOST=172.22.0.61"
    set "BE_PROD_DB_PORT=5432"
    set "BE_PROD_DB_USER=postgres"
    set "BE_PROD_DB_PASSWORD=postgres"
    set "BE_PROD_DB_NAME=app_db_prod"
)

echo Menyalakan service infra utama (!DB_ENGINE!)...
call :compose_local up -d --force-recreate redis_dev redis_prod !DB_SERVICES! jenkins
call :abort_if_failed "Menyalakan service infra utama"
if errorlevel 1 goto exit
call :compose_local up -d --wait redis_dev redis_prod !DB_SERVICES!
call :abort_if_failed "Menunggu DB dan Redis siap"
if errorlevel 1 goto exit
call :compose_local up -d --wait jenkins
call :abort_if_failed "Menunggu CI/CD siap"
if errorlevel 1 goto exit
echo.
echo Menyalakan MinIO jika port tersedia...
call :compose_local up -d --force-recreate minio
if errorlevel 1 (
    echo Peringatan: MinIO gagal dijalankan. Periksa konflik port 9000 atau 9001.
)
echo Menyalakan Netdata jika port tersedia...
call :compose_local up -d --force-recreate netdata
if errorlevel 1 (
    echo Peringatan: Netdata gagal dijalankan. Periksa konflik port %NETDATA_PORT%.
)
echo.
echo Memastikan image backend dan CI/CD terbaru...
call :compose_local build jenkins be_dev be_prod
call :abort_if_failed "Membangun image backend dan CI/CD"
if errorlevel 1 goto exit
echo.
echo Menyalakan service aplikasi...
call :compose_local up -d --force-recreate be_dev be_prod fe_dev fe_prod nginx
call :abort_if_failed "Menyalakan service aplikasi"
if errorlevel 1 goto exit
call :compose_local up -d --wait be_dev be_prod
call :abort_if_failed "Menunggu backend dev dan prod siap"
if errorlevel 1 goto exit
call :ensure_wsl_keepalive
call :abort_if_failed "Menjaga sesi WSL tetap aktif untuk akses host Windows"
if errorlevel 1 goto exit
call :sync_windows_wsl_host_ports
call :abort_if_failed "Sinkronisasi port Windows ke WSL"
if errorlevel 1 goto exit
call :wait_gateway_health "dev" "%LOCAL_API_DEV_HEALTH_URL%" 30
call :abort_if_failed "Menunggu gateway API dev siap"
if errorlevel 1 goto exit
call :wait_gateway_health "prod" "%LOCAL_API_PROD_HEALTH_URL%" 30
call :abort_if_failed "Menunggu gateway API prod siap"
if errorlevel 1 goto exit
call :wait_web_url "cicd" "%LOCAL_CICD_LOGIN_URL%" 40 "Jenkins"
call :abort_if_failed "Menunggu gateway CI/CD siap"
if errorlevel 1 goto exit
echo.
echo Publish FE dev...
call :publish_frontend fe_dev dev /publish/dev
call :abort_if_failed "Publish FE dev"
if errorlevel 1 goto exit
echo Publish FE prod...
call :publish_frontend fe_prod prod /publish/prod
call :abort_if_failed "Publish FE prod"
if errorlevel 1 goto exit
call :print_local_urls
if defined BATCH_DIRECT goto exit
pause
goto menu

:run_local_minimal
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo LABEL run_local_minimal
)
echo Build dan publish FE dev tanpa rebuild image...
call :compose_local up -d postgres_dev redis_dev be_dev fe_dev nginx
call :abort_if_failed "Menjalankan dependency FE dev"
if errorlevel 1 goto exit
call :compose_local up -d --wait postgres_dev redis_dev be_dev
call :abort_if_failed "Menunggu API dev siap"
if errorlevel 1 goto exit
call :ensure_wsl_keepalive
call :abort_if_failed "Menjaga sesi WSL tetap aktif untuk akses host Windows"
if errorlevel 1 goto exit
call :sync_windows_wsl_host_ports
call :abort_if_failed "Sinkronisasi port Windows ke WSL"
if errorlevel 1 goto exit
call :publish_frontend fe_dev dev /publish/dev
if errorlevel 1 (
    call :compose_local up -d --wait fe_dev
    call :abort_if_failed "Menunggu FE dev siap"
    if errorlevel 1 goto exit
    call :publish_frontend fe_dev dev /publish/dev
    call :abort_if_failed "Publish FE dev"
    if errorlevel 1 goto exit
) else (
    echo FE dev source berhasil dipublish ulang tanpa restart nginx.
)
echo.
echo FE dev aktif.
echo.
echo FE dev       : %LOCAL_FE_DEV_URL%
echo API dev      : %LOCAL_API_DEV_HEALTH_URL%
echo CI/CD        : %LOCAL_CICD_URL%
echo Client LAN   : http://IP_SERVER:%NGINX_HTTP_PORT%/dev/
echo.
echo Catatan:
echo   Perubahan code FE tetap tanpa rebuild image Docker.
echo   Jika FE dev sudah running, refresh code dilakukan dengan publish static file.
echo   Nginx tetap hidup dan tidak lagi bergantung pada vite preview saat refresh.
echo   Hasil build aktif FE dev ada di docker\runtime\local\fe_dev_public\current
if defined BATCH_DIRECT goto exit
pause
goto menu

:run_be_dev
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo LABEL run_be_dev
)
echo Menjalankan atau memastikan BE dev sehat tanpa rebuild image...
call :compose_local up -d postgres_dev redis_dev nginx
call :abort_if_failed "Menjalankan dependency BE dev"
if errorlevel 1 goto exit
call :compose_local up -d --wait postgres_dev redis_dev
call :abort_if_failed "Menunggu dependency BE dev siap"
if errorlevel 1 goto exit
call :compose_local up -d --force-recreate --no-deps be_dev
call :abort_if_failed "Refresh container BE dev"
if errorlevel 1 goto exit
call :ensure_wsl_keepalive
call :abort_if_failed "Menjaga sesi WSL tetap aktif untuk akses host Windows"
if errorlevel 1 goto exit
call :sync_windows_wsl_host_ports
call :abort_if_failed "Sinkronisasi port Windows ke WSL"
if errorlevel 1 goto exit
call :wait_gateway_health "dev" "%LOCAL_API_DEV_HEALTH_URL%" 30
call :abort_if_failed "Menunggu gateway API dev siap"
if errorlevel 1 goto exit
echo.
echo Akses:
echo   API dev : %LOCAL_API_DEV_HEALTH_URL%
echo   CI/CD   : %LOCAL_CICD_URL%
echo   LAN     : http://IP_SERVER:%NGINX_HTTP_PORT%/dev/api/health
echo   FE dev  : %LOCAL_FE_DEV_URL%
if /I "%DOCKER_RUNNER%"=="wsl" echo   WSL     : http://IP_WSL:%NGINX_HTTP_PORT%/dev/
echo.
echo Catatan:
echo   Perubahan code BE diambil dari source host tanpa rebuild image.
echo   Menu ini merecreate container backend agar code terbaru langsung aktif.
if defined BATCH_DIRECT goto exit
pause
goto menu

:run_fe_prod
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo LABEL run_fe_prod
)
echo Build dan publish FE prod tanpa rebuild image...
call :compose_local up -d postgres_prod redis_prod be_prod fe_prod nginx
call :abort_if_failed "Menjalankan dependency FE prod"
if errorlevel 1 goto exit
call :compose_local up -d --wait postgres_prod redis_prod be_prod
call :abort_if_failed "Menunggu API prod siap"
if errorlevel 1 goto exit
call :ensure_wsl_keepalive
call :abort_if_failed "Menjaga sesi WSL tetap aktif untuk akses host Windows"
if errorlevel 1 goto exit
call :sync_windows_wsl_host_ports
call :abort_if_failed "Sinkronisasi port Windows ke WSL"
if errorlevel 1 goto exit
call :publish_frontend fe_prod prod /publish/prod
if errorlevel 1 (
    call :compose_local up -d --wait fe_prod
    call :abort_if_failed "Menunggu FE prod siap"
    if errorlevel 1 goto exit
    call :publish_frontend fe_prod prod /publish/prod
    call :abort_if_failed "Publish FE prod"
    if errorlevel 1 goto exit
) else (
    echo FE prod source berhasil dipublish ulang tanpa restart nginx.
)
echo.
echo Akses:
echo   FE prod : %LOCAL_FE_PROD_URL%
echo   CI/CD   : %LOCAL_CICD_URL%
echo.
echo Catatan:
echo   FE prod lokal sekarang publish ulang dari source code tanpa rebuild image.
echo   Hasil build aktif FE prod ada di docker\runtime\local\fe_prod_public\current
echo   Gunakan menu recreate service jika package.json atau Dockerfile berubah.
if defined BATCH_DIRECT goto exit
pause
goto menu

:run_be_prod
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo LABEL run_be_prod
)
echo Menjalankan atau memastikan BE prod sehat tanpa rebuild image...
call :compose_local up -d postgres_prod redis_prod nginx
call :abort_if_failed "Menjalankan dependency BE prod"
if errorlevel 1 goto exit
call :compose_local up -d --wait postgres_prod redis_prod
call :abort_if_failed "Menunggu dependency BE prod siap"
if errorlevel 1 goto exit
call :compose_local up -d --force-recreate --no-deps be_prod
call :abort_if_failed "Refresh container BE prod"
if errorlevel 1 goto exit
call :ensure_wsl_keepalive
call :abort_if_failed "Menjaga sesi WSL tetap aktif untuk akses host Windows"
if errorlevel 1 goto exit
call :sync_windows_wsl_host_ports
call :abort_if_failed "Sinkronisasi port Windows ke WSL"
if errorlevel 1 goto exit
call :wait_gateway_health "prod" "%LOCAL_API_PROD_HEALTH_URL%" 30
call :abort_if_failed "Menunggu gateway API prod siap"
if errorlevel 1 goto exit
echo.
echo Akses:
echo   API prod : %LOCAL_API_PROD_HEALTH_URL%
echo   CI/CD    : %LOCAL_CICD_URL%
echo.
echo Catatan:
echo   Perubahan code BE diambil dari source host tanpa rebuild image.
echo   Menu ini merecreate container backend agar code terbaru langsung aktif.
if defined BATCH_DIRECT goto exit
pause
goto menu

:run_start_service_direct
call :resolve_local_service_token "%DIRECT_SERVICE%"
if not defined SELECTED_SERVICE (
    echo Service untuk worker start tidak valid.
    if defined BATCH_DIRECT goto exit
    goto menu
)
if /I "!SELECTED_SERVICE!"=="jenkins" goto run_start_jenkins_direct
echo Menjalankan service !SELECTED_SERVICE!...
call :compose_local up -d !SELECTED_SERVICE!
call :abort_if_failed "Menjalankan service !SELECTED_SERVICE!"
if defined BATCH_DIRECT goto exit
pause
goto menu

:run_start_jenkins_direct
echo Menjalankan Jenkins beserta gateway CI/CD...
call :compose_local up -d nginx jenkins
call :abort_if_failed "Menjalankan Jenkins dan gateway CI/CD"
if errorlevel 1 goto exit
call :ensure_wsl_keepalive
call :abort_if_failed "Menjaga sesi WSL tetap aktif untuk akses Jenkins"
if errorlevel 1 goto exit
call :sync_windows_wsl_host_ports
call :abort_if_failed "Sinkronisasi port Windows ke WSL untuk Jenkins"
if errorlevel 1 goto exit
call :wait_web_url "jenkins-direct" "%LOCAL_JENKINS_DIRECT_URL%" 80 "Jenkins"
call :abort_if_failed "Menunggu Jenkins direct siap"
if errorlevel 1 goto exit
call :wait_web_url "cicd" "%LOCAL_CICD_LOGIN_URL%" 80 "Jenkins"
call :abort_if_failed "Menunggu gateway CI/CD siap"
if errorlevel 1 goto exit
echo.
echo Akses:
echo   Jenkins direct : %LOCAL_JENKINS_DIRECT_URL%
echo   CI/CD gateway  : %LOCAL_CICD_URL%
echo.
echo Catatan:
echo   Flow Jenkins sekarang otomatis menjaga WSL tetap hidup selama launcher aktif.
echo   Nginx ikut dinyalakan agar URL gateway /cicd/ langsung bisa dipakai.
if defined BATCH_DIRECT goto exit
pause
goto menu

:start_service_menu
cls
echo ================================================
echo Start Service ^(stack lokal utama^)
echo ================================================
call :print_local_service_list
set /p service_choice="Pilih service [1-16], atau B untuk kembali: "

if /I "%service_choice%"=="B" goto menu
call :resolve_local_service "%service_choice%"
if not defined SELECTED_SERVICE (
    echo Pilihan service tidak valid.
    pause
    goto start_service_menu
)

echo Menjalankan service !SELECTED_SERVICE! di background...
call :launch_background "6" "!SELECTED_SERVICE!"
call :abort_and_exit_if_failed "Menjalankan service !SELECTED_SERVICE! di background"
echo Service !SELECTED_SERVICE! akan dijalankan di background.
goto exit

:stop_service_menu
cls
echo ================================================
echo Stop Service ^(stack lokal utama^)
echo ================================================
call :print_local_service_list
set /p service_choice="Pilih service [1-16], atau B untuk kembali: "

if /I "%service_choice%"=="B" goto menu
call :resolve_local_service "%service_choice%"
if not defined SELECTED_SERVICE (
    echo Pilihan service tidak valid.
    pause
    goto stop_service_menu
)

echo Menghentikan service !SELECTED_SERVICE!...
call :compose_local stop !SELECTED_SERVICE!
call :abort_if_failed "Menghentikan service !SELECTED_SERVICE!"
pause
goto menu

:rebuild_service_menu
cls
echo ================================================
echo Recreate Service ^(stack lokal utama^)
echo ================================================
echo Service yang layak direcreate:
echo [1] be_dev
echo [2] be_prod
echo [3] fe_dev
echo [4] fe_prod
echo [5] nginx
echo [6] jenkins
echo [B] Kembali
echo ================================================
set /p service_choice="Pilih service [1-6], atau B untuk kembali: "

if /I "%service_choice%"=="B" goto menu

if "%service_choice%"=="1" set "SELECTED_SERVICE=be_dev"
if "%service_choice%"=="2" set "SELECTED_SERVICE=be_prod"
if "%service_choice%"=="3" set "SELECTED_SERVICE=fe_dev"
if "%service_choice%"=="4" set "SELECTED_SERVICE=fe_prod"
if "%service_choice%"=="5" set "SELECTED_SERVICE=nginx"
if "%service_choice%"=="6" set "SELECTED_SERVICE=jenkins"

if not defined SELECTED_SERVICE (
    echo Pilihan service tidak valid.
    pause
    goto rebuild_service_menu
)

echo Recreate service !SELECTED_SERVICE!...
call :compose_local up -d --force-recreate --no-deps !SELECTED_SERVICE!
call :abort_if_failed "Menjalankan ulang service !SELECTED_SERVICE!"
pause
goto menu

:status_menu
cls
echo ================================================
echo Cek Status Container
echo ================================================
echo [1] Stack lokal utama
echo [B] Kembali
echo ================================================
set /p status_choice="Pilih opsi [1], atau B untuk kembali: "

if /I "%status_choice%"=="B" goto menu
if "%status_choice%"=="1" (
    goto show_status_local
)

echo Pilihan tidak valid.
pause
goto status_menu

:show_status_local
call :docker_local_ps
call :abort_if_failed "Cek status stack lokal utama"
if defined BATCH_DIRECT goto exit
pause
goto menu

:logs_menu
cls
echo ================================================
echo Lihat Logs Container ^(stack lokal utama^)
echo ================================================
echo [0] Semua service stack lokal utama
call :print_local_service_list
echo [B] Kembali
echo ================================================
set /p logs_choice="Pilih service [0-13], atau B untuk kembali: "

if /I "%logs_choice%"=="B" goto menu
if "%logs_choice%"=="0" (
    call :compose_local logs --tail=200
    call :abort_if_failed "Lihat log stack lokal utama"
    pause
    goto menu
)

call :resolve_local_service "%logs_choice%"
if not defined SELECTED_SERVICE (
    echo Pilihan service tidak valid.
    pause
    goto logs_menu
)

call :compose_local logs --tail=200 !SELECTED_SERVICE!
call :abort_if_failed "Lihat log service !SELECTED_SERVICE!"
pause
goto menu

:stop_local
echo Menghentikan stack lokal utama...
call :compose_local down --remove-orphans
call :abort_if_failed "Menghentikan stack lokal utama"
call :stop_wsl_keepalive
call :stop_windows_host_bridge
pause
goto menu

:stop_all
echo Menghentikan semua stack proyek...
call :compose_local down --remove-orphans
call :stop_wsl_keepalive
call :stop_windows_host_bridge
pause
goto menu

:show_urls
cls
call :print_local_urls
pause
goto menu

:open_run_guide
if exist "%cd%\docker_run.md" (
    start "" "%cd%\docker_run.md"
) else (
    echo File docker_run.md tidak ditemukan.
    pause
)
goto menu

:print_local_urls
echo.
echo ================================================
echo MODE LOKAL UTAMA
echo ================================================
echo FE dev          : %LOCAL_FE_DEV_URL%
echo FE prod         : %LOCAL_FE_PROD_URL%
echo API dev health  : %LOCAL_API_DEV_HEALTH_URL%
echo API prod health : %LOCAL_API_PROD_HEALTH_URL%
echo CI/CD           : %LOCAL_CICD_URL%
echo PgAdmin         : %LOCAL_PGADMIN_URL%
echo PhpMyAdmin      : %LOCAL_PHPMYADMIN_URL%
echo MinIO API       : %LOCAL_MINIO_API_URL%
echo MinIO Console   : %LOCAL_MINIO_CONSOLE_URL%
echo Netdata         : %LOCAL_NETDATA_URL%
echo.
echo Catatan akses:
echo   Login Jenkins memakai admin / qwerty123.
echo   Jika merespons 403 sebelum login, itu berarti servicenya tetap terjangkau.
echo   PgAdmin diharapkan membuka halaman login.
echo   MinIO hanya bisa diakses jika port 9000 and 9001 tidak bentrok dengan proses lain.
echo   Netdata berjalan langsung di port %NETDATA_PORT%.
echo.
echo Dari client LAN:
echo   FE dev  : http://IP_SERVER:%NGINX_HTTP_PORT%/dev/
echo   FE prod : http://IP_SERVER:%NGINX_HTTP_PORT%/prod/
echo   CI/CD     : http://IP_SERVER:%NGINX_HTTP_PORT%/cicd/
echo   PhpMyAdmin : http://IP_SERVER:%PHPMYADMIN_PORT%/
echo.
echo Jika Docker dijalankan di WSL dan localhost tidak tembus dari Windows:
echo   Gunakan 127.0.0.1 dulu sebelum mencoba localhost.
echo   FE dev  : http://IP_WSL:%NGINX_HTTP_PORT%/dev/
echo   CI/CD     : http://IP_WSL:%NGINX_HTTP_PORT%/cicd/
echo ================================================
exit /b

:print_local_service_list
echo [1] redis_dev
echo [2] redis_prod
echo [3] postgres_dev
echo [4] postgres_prod
echo [5] pgadmin
echo [6] minio
echo [7] be_dev
echo [8] be_prod
echo [9] fe_dev
echo [10] fe_prod
echo [11] nginx
echo [12] jenkins
echo [13] mysql_dev
echo [14] mysql_prod
echo [15] phpmyadmin
echo [16] netdata
exit /b

:resolve_local_service
set "SELECTED_SERVICE="
if "%~1"=="1" set "SELECTED_SERVICE=redis_dev"
if "%~1"=="2" set "SELECTED_SERVICE=redis_prod"
if "%~1"=="3" set "SELECTED_SERVICE=postgres_dev"
if "%~1"=="4" set "SELECTED_SERVICE=postgres_prod"
if "%~1"=="5" set "SELECTED_SERVICE=pgadmin"
if "%~1"=="6" set "SELECTED_SERVICE=minio"
if "%~1"=="7" set "SELECTED_SERVICE=be_dev"
if "%~1"=="8" set "SELECTED_SERVICE=be_prod"
if "%~1"=="9" set "SELECTED_SERVICE=fe_dev"
if "%~1"=="10" set "SELECTED_SERVICE=fe_prod"
if "%~1"=="11" set "SELECTED_SERVICE=nginx"
if "%~1"=="12" set "SELECTED_SERVICE=jenkins"
if "%~1"=="13" set "SELECTED_SERVICE=mysql_dev"
if "%~1"=="14" set "SELECTED_SERVICE=mysql_prod"
if "%~1"=="15" set "SELECTED_SERVICE=phpmyadmin"
if "%~1"=="16" set "SELECTED_SERVICE=netdata"
exit /b

:resolve_local_service_token
set "SELECTED_SERVICE="
call :resolve_local_service "%~1"
if defined SELECTED_SERVICE exit /b

if /I "%~1"=="redis" set "SELECTED_SERVICE=redis_dev"
if /I "%~1"=="redis_dev" set "SELECTED_SERVICE=redis_dev"
if /I "%~1"=="redis_prod" set "SELECTED_SERVICE=redis_prod"
if /I "%~1"=="postgres" set "SELECTED_SERVICE=postgres_dev"
if /I "%~1"=="postgres_dev" set "SELECTED_SERVICE=postgres_dev"
if /I "%~1"=="postgres_prod" set "SELECTED_SERVICE=postgres_prod"
if /I "%~1"=="pgadmin" set "SELECTED_SERVICE=pgadmin"
if /I "%~1"=="minio" set "SELECTED_SERVICE=minio"
if /I "%~1"=="be_dev" set "SELECTED_SERVICE=be_dev"
if /I "%~1"=="be_prod" set "SELECTED_SERVICE=be_prod"
if /I "%~1"=="fe_dev" set "SELECTED_SERVICE=fe_dev"
if /I "%~1"=="fe_prod" set "SELECTED_SERVICE=fe_prod"
if /I "%~1"=="nginx" set "SELECTED_SERVICE=nginx"
if /I "%~1"=="jenkins" set "SELECTED_SERVICE=jenkins"
if /I "%~1"=="mysql_dev" set "SELECTED_SERVICE=mysql_dev"
if /I "%~1"=="mysql_prod" set "SELECTED_SERVICE=mysql_prod"
if /I "%~1"=="phpmyadmin" set "SELECTED_SERVICE=phpmyadmin"
if /I "%~1"=="netdata" set "SELECTED_SERVICE=netdata"
exit /b

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
echo Silakan pasang Docker atau jalankan lewat environment yang punya Docker.
pause
exit /b 1

:compose_local
set "COMPOSE_ARGS=%*"
call :run_compose "docker compose %COMPOSE_ARGS%"
exit /b %ERRORLEVEL%

:docker_local_ps
call :run_docker "ps -a"
exit /b %ERRORLEVEL%

:publish_frontend
set "PUBLISH_SERVICE=%~1"
set "PUBLISH_MODE=%~2"
set "PUBLISH_TARGET=%~3"
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo LABEL publish_frontend %PUBLISH_SERVICE% %PUBLISH_MODE% %PUBLISH_TARGET%
)
if /I "%DOCKER_RUNNER%"=="windows" (
    call docker compose exec -T %PUBLISH_SERVICE% sh -lc "node ./scripts/docker_publish_frontend.js %PUBLISH_MODE% %PUBLISH_TARGET%"
    exit /b %ERRORLEVEL%
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "$cmd = 'cd ""%DOCKER_DIR_WSL%""; docker compose exec -T %PUBLISH_SERVICE% sh -lc ""node ./scripts/docker_publish_frontend.js %PUBLISH_MODE% %PUBLISH_TARGET%""'; & wsl -e sh -lc $cmd; exit $LASTEXITCODE"
exit /b %ERRORLEVEL%

:launch_background
set "BACKGROUND_CHOICE=%~1"
set "BACKGROUND_SERVICE=%~2"

if defined BACKGROUND_SERVICE (
    set "RUN_BAT_WORKER_ARGS=--worker %BACKGROUND_CHOICE% %BACKGROUND_SERVICE%"
) else (
    set "RUN_BAT_WORKER_ARGS=--worker %BACKGROUND_CHOICE%"
)

if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo LAUNCH %RUN_BAT_WORKER_ARGS%
)

rem Use a dedicated cmd child so worker keeps running after this launcher exits.
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$scriptPath = $env:RUN_BAT_SELF; $argsLine = $env:RUN_BAT_WORKER_ARGS; $workerCommand = ('\"' + $scriptPath + '\" ' + $argsLine); Start-Process -FilePath 'cmd.exe' -WindowStyle Hidden -ArgumentList @('/d','/c',$workerCommand) | Out-Null"
set "RUN_BAT_WORKER_ARGS="
exit /b %ERRORLEVEL%

:run_compose
set "FULL_COMPOSE_CMD=%~1"
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo LABEL run_compose %FULL_COMPOSE_CMD%
)
if /I "%DOCKER_RUNNER%"=="windows" (
    call %FULL_COMPOSE_CMD%
    exit /b %ERRORLEVEL%
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "$cmd = 'cd ""%DOCKER_DIR_WSL%""; %FULL_COMPOSE_CMD%'; & wsl -e sh -lc $cmd; exit $LASTEXITCODE"
exit /b %ERRORLEVEL%

:run_docker
set "FULL_DOCKER_CMD=docker %~1"
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo LABEL run_docker %FULL_DOCKER_CMD%
)
if /I "%DOCKER_RUNNER%"=="windows" (
    call %FULL_DOCKER_CMD%
    exit /b %ERRORLEVEL%
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "$cmd = 'cd ""%DOCKER_DIR_WSL%""; %FULL_DOCKER_CMD%'; & wsl -e sh -lc $cmd; exit $LASTEXITCODE"
exit /b %ERRORLEVEL%

:sync_windows_wsl_host_ports
if defined RUN_BAT_TEST_SKIP_PORTPROXY exit /b 0
if /I not "%DOCKER_RUNNER%"=="wsl" exit /b 0
findstr /I /C:"networkingMode=mirrored" "%USERPROFILE%\.wslconfig" >nul 2>&1
if not errorlevel 1 exit /b 0
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo HOST_BRIDGE_SYNC
)
call :stop_windows_host_bridge
if exist "%PORTPROXY_SYNC_SCRIPT%" (
    if defined RUN_BAT_TEST_LOG (
        >> "%RUN_BAT_TEST_LOG%" echo PORTPROXY_SYNC "%PORTPROXY_SYNC_SCRIPT%"
    )
    powershell -NoProfile -ExecutionPolicy Bypass -File "%PORTPROXY_SYNC_SCRIPT%" >nul 2>&1
    if not errorlevel 1 exit /b 0
)
call :launch_windows_host_bridge
exit /b %ERRORLEVEL%

:launch_windows_host_bridge
if defined RUN_BAT_TEST_SKIP_PORTPROXY exit /b 0
if /I not "%DOCKER_RUNNER%"=="wsl" exit /b 0
findstr /I /C:"networkingMode=mirrored" "%USERPROFILE%\.wslconfig" >nul 2>&1
if not errorlevel 1 exit /b 0
if not exist "%WINDOWS_PORT_BRIDGE_SCRIPT%" (
    echo File windows_port_bridge.py tidak ditemukan.
    exit /b 1
)
call :resolve_current_wsl_ip
if errorlevel 1 (
    echo IP WSL aktif tidak bisa dibaca.
    exit /b 1
)
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo HOST_BRIDGE_LAUNCH "%WINDOWS_PORT_BRIDGE_SCRIPT%"
)
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath 'python' -WindowStyle Hidden -ArgumentList @('\"%WINDOWS_PORT_BRIDGE_SCRIPT%\"','--wsl-ip','!CURRENT_WSL_IP!') | Out-Null"
exit /b %ERRORLEVEL%

:stop_windows_host_bridge
if defined RUN_BAT_TEST_SKIP_PORTPROXY exit /b 0
findstr /I /C:"networkingMode=mirrored" "%USERPROFILE%\.wslconfig" >nul 2>&1
if not errorlevel 1 exit /b 0
if not exist "%WINDOWS_PORT_BRIDGE_SCRIPT%" exit /b 0
if exist "%WINDOWS_PORT_BRIDGE_PID%" (
    if defined RUN_BAT_TEST_LOG (
        >> "%RUN_BAT_TEST_LOG%" echo HOST_BRIDGE_STOP "%WINDOWS_PORT_BRIDGE_SCRIPT%"
    )
    powershell -NoProfile -ExecutionPolicy Bypass -Command "& 'python' '%WINDOWS_PORT_BRIDGE_SCRIPT%' --stop" >nul 2>&1
)
exit /b 0

:ensure_wsl_keepalive
if defined RUN_BAT_TEST_SKIP_PORTPROXY exit /b 0
if /I not "%DOCKER_RUNNER%"=="wsl" exit /b 0
if exist "%WSL_KEEPALIVE_PID%" (
    for /f "usebackq delims=" %%i in ("%WSL_KEEPALIVE_PID%") do set "CURRENT_KEEPALIVE_PID=%%i"
    if defined CURRENT_KEEPALIVE_PID (
        powershell -NoProfile -ExecutionPolicy Bypass -Command "if (Get-Process -Id %CURRENT_KEEPALIVE_PID% -ErrorAction SilentlyContinue) { exit 0 } else { exit 1 }" >nul 2>&1
        if not errorlevel 1 exit /b 0
    )
)
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo WSL_KEEPALIVE_START
)
powershell -NoProfile -ExecutionPolicy Bypass -Command "$p = Start-Process -FilePath \"$env:WINDIR\\System32\\wsl.exe\" -ArgumentList @('-e','sh','-lc','sleep 2147483647') -WindowStyle Hidden -PassThru; Set-Content -Path '%WSL_KEEPALIVE_PID%' -Value $p.Id"
exit /b %ERRORLEVEL%

:stop_wsl_keepalive
if defined RUN_BAT_TEST_SKIP_PORTPROXY exit /b 0
if not exist "%WSL_KEEPALIVE_PID%" exit /b 0
for /f "usebackq delims=" %%i in ("%WSL_KEEPALIVE_PID%") do set "CURRENT_KEEPALIVE_PID=%%i"
if defined CURRENT_KEEPALIVE_PID (
    if defined RUN_BAT_TEST_LOG (
        >> "%RUN_BAT_TEST_LOG%" echo WSL_KEEPALIVE_STOP
    )
    powershell -NoProfile -ExecutionPolicy Bypass -Command "Stop-Process -Id %CURRENT_KEEPALIVE_PID% -Force -ErrorAction SilentlyContinue" >nul 2>&1
)
del /f /q "%WSL_KEEPALIVE_PID%" >nul 2>&1
exit /b 0

:resolve_current_wsl_ip
set "CURRENT_WSL_IP="
for /f "usebackq delims=" %%i in (`powershell -NoProfile -ExecutionPolicy Bypass -Command "$output = wsl -e sh -lc 'hostname -I'; $tokens = ($output -split '\s+') | Where-Object { $_ -match '^\d{1,3}(\.\d{1,3}){3}$' }; $preferred = $tokens | Where-Object { $_ -like '172.*' -or $_ -like '10.*' -or $_ -like '192.168.*' } | Select-Object -First 1; if (-not $preferred) { $preferred = $tokens | Select-Object -First 1 }; if ($preferred) { Write-Output $preferred }"` ) do set "CURRENT_WSL_IP=%%i"
if not defined CURRENT_WSL_IP exit /b 1
exit /b 0

:wait_gateway_health
set "WAIT_ENV=%~1"
set "WAIT_URL=%~2"
set "WAIT_ATTEMPTS=%~3"
if not defined WAIT_ATTEMPTS set "WAIT_ATTEMPTS=30"
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo WAIT_GATEWAY %WAIT_ENV% %WAIT_URL%
)
if defined RUN_BAT_TEST_FAIL_GATEWAY_WAIT exit /b 1
if defined RUN_BAT_TEST_SKIP_HTTP_WAIT exit /b 0

powershell -NoProfile -ExecutionPolicy Bypass -Command "$uri = '%WAIT_URL%'; $attempts = %WAIT_ATTEMPTS%; $ok = $false; for ($i = 0; $i -lt $attempts; $i++) { try { $response = Invoke-WebRequest -UseBasicParsing -Uri $uri -TimeoutSec 3; if ($response.Content -match '\"status\"\\s*:\\s*\"ok\"') { $ok = $true; break } } catch { $httpResponse = $_.Exception.Response; if ($httpResponse) { $statusCode = [int]$httpResponse.StatusCode; if ($statusCode -in 502, 503, 504) { Start-Sleep -Seconds 2; continue } } } Start-Sleep -Seconds 2 }; if (-not $ok) { exit 1 }"
exit /b %ERRORLEVEL%

:wait_web_url
set "WAIT_KEY=%~1"
set "WAIT_URL=%~2"
set "WAIT_ATTEMPTS=%~3"
set "WAIT_MATCH=%~4"
if not defined WAIT_ATTEMPTS set "WAIT_ATTEMPTS=30"
if defined RUN_BAT_TEST_LOG (
    >> "%RUN_BAT_TEST_LOG%" echo WAIT_WEB %WAIT_KEY% %WAIT_URL%
)
if defined RUN_BAT_TEST_SKIP_HTTP_WAIT exit /b 0

powershell -NoProfile -ExecutionPolicy Bypass -Command "$uri = '%WAIT_URL%'; $attempts = %WAIT_ATTEMPTS%; $needle = '%WAIT_MATCH%'; $ok = $false; for ($i = 0; $i -lt $attempts; $i++) { try { $response = Invoke-WebRequest -UseBasicParsing -Uri $uri -TimeoutSec 4 -MaximumRedirection 0; if (($response.StatusCode -eq 200 -or $response.StatusCode -eq 403) -and $response.Content -match $needle) { $ok = $true; break } } catch { $httpResponse = $_.Exception.Response; if ($httpResponse) { $statusCode = [int]$httpResponse.StatusCode; if ($statusCode -in 301,302,307,308,404,502,503,504) { Start-Sleep -Seconds 2; continue } } } Start-Sleep -Seconds 2 }; if (-not $ok) { exit 1 }"
exit /b %ERRORLEVEL%

:abort_and_exit_if_failed
if errorlevel 1 (
    echo.
    echo Gagal: %~1
    set "SCRIPT_EXIT_CODE=1"
)
goto exit

:abort_if_failed
if errorlevel 1 (
    echo.
    echo Gagal: %~1
    set "SCRIPT_EXIT_CODE=1"
    if /I "%DOCKER_RUNNER%"=="wsl" (
        echo Docker dijalankan lewat WSL pada: %DOCKER_DIR_WSL%
    )
    if defined BATCH_DIRECT exit /b 1
    pause
    goto menu
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
