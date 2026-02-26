@echo off
setlocal enabledelayedexpansion
chcp 65001 >nul 2>&1
title CFE PRO - Instalador del Sistema v1.0
color 0B
cls

echo.
echo  ============================================================
echo    CFE PRO - Sistema de Facturacion
echo    Instalador Automatico v1.0
echo  ============================================================
echo.
echo  Este instalador configurara todo lo necesario:
echo    - Servidor PHP (incluido)
echo    - Base de datos MySQL (incluida)
echo    - Sistema de Facturacion
echo    - Accesos directos
echo.
echo  Presione cualquier tecla para comenzar...
pause >nul

REM ===== Definir rutas =====
set "PROJECT_DIR=%~dp0"
set "PHP_EXE=%PROJECT_DIR%xampp\php\php.exe"
set "MYSQL_DIR=%PROJECT_DIR%xampp\mysql"
set "MYSQLD_EXE=%MYSQL_DIR%\bin\mysqld.exe"
set "MYSQL_EXE=%MYSQL_DIR%\bin\mysql.exe"
set "DB_NAME=sistema_facturacion_2"
set "DB_USER=root"
set "DB_PASS="
set "SQL_FILE=%PROJECT_DIR%scripts\installer\database.sql"
set "ENV_FILE=%PROJECT_DIR%.env"

REM ===== PASO 1: Verificar PHP =====
cls
echo.
echo  [Paso 1/6] Verificando PHP...
echo.

if not exist "%PHP_EXE%" (
    echo  [ERROR] PHP no encontrado en: %PHP_EXE%
    echo  Asegurese de que la carpeta xampp\php existe.
    pause
    exit /b 1
)

for /f "tokens=*" %%V in ('"%PHP_EXE%" -r "echo PHP_VERSION;"') do set PHP_VER=%%V
echo  [OK] PHP encontrado: v%PHP_VER%

REM Verificar/habilitar extensiones
"%PHP_EXE%" -r "if(!extension_loaded('pdo_mysql')){echo 'MISSING';}" > "%TEMP%\php_ext_check.tmp" 2>nul
set /p EXT_CHECK=<"%TEMP%\php_ext_check.tmp"
del "%TEMP%\php_ext_check.tmp" >nul 2>&1

if "%EXT_CHECK%"=="MISSING" (
    echo  [..] Habilitando extension pdo_mysql en php.ini...
    powershell -Command "(Get-Content '%PROJECT_DIR%xampp\php\php.ini') -replace ';extension=pdo_mysql', 'extension=pdo_mysql' | Set-Content '%PROJECT_DIR%xampp\php\php.ini'"
    echo  [OK] Extension habilitada.
) else (
    echo  [OK] Extensiones PHP verificadas.
)
timeout /t 2 /nobreak >nul

REM ===== PASO 2: Verificar/Iniciar MySQL =====
cls
echo.
echo  [Paso 2/6] Verificando MySQL...
echo.

if not exist "%MYSQLD_EXE%" (
    echo  [ERROR] MySQL no encontrado en: %MYSQLD_EXE%
    pause
    exit /b 1
)

echo  [OK] MySQL encontrado.

REM Verificar si ya esta corriendo
"%MYSQL_EXE%" --user=%DB_USER% --host=127.0.0.1 --port=3306 -e "SELECT 1;" >nul 2>&1
if %errorlevel% equ 0 (
    echo  [OK] MySQL ya esta corriendo.
    goto :mysql_ready
)

echo  [..] Iniciando MySQL...
start "" /B "%MYSQLD_EXE%" --basedir="%MYSQL_DIR%" --datadir="%MYSQL_DIR%\data" --port=3306

echo  [..] Esperando que MySQL inicie...
set ATTEMPTS=0

:wait_mysql
timeout /t 2 /nobreak >nul
set /a ATTEMPTS+=1
"%MYSQL_EXE%" --user=%DB_USER% --host=127.0.0.1 --port=3306 -e "SELECT 1;" >nul 2>&1
if %errorlevel% equ 0 (
    echo  [OK] MySQL iniciado correctamente.
    goto :mysql_ready
)
if %ATTEMPTS% lss 15 goto :wait_mysql

echo  [ERROR] MySQL no pudo iniciarse despues de 30 segundos.
pause
exit /b 1

:mysql_ready
timeout /t 1 /nobreak >nul

REM ===== PASO 3: Crear Base de Datos =====
cls
echo.
echo  [Paso 3/6] Configurando Base de Datos...
echo.

"%MYSQL_EXE%" --user=%DB_USER% --host=127.0.0.1 --port=3306 -e "USE %DB_NAME%;" >nul 2>&1
if %errorlevel% equ 0 (
    echo  [OK] Base de datos '%DB_NAME%' ya existe.
    echo.
    echo  La base de datos ya tiene datos. Que desea hacer?
    echo    1) Mantener datos actuales (recomendado)
    echo    2) Reinstalar base de datos (BORRA datos)
    echo.
    set /p DB_CHOICE="  Seleccione (1/2): "
    if "!DB_CHOICE!"=="2" (
        echo  [..] Eliminando base de datos...
        "%MYSQL_EXE%" --user=%DB_USER% --host=127.0.0.1 --port=3306 -e "DROP DATABASE `%DB_NAME%`;" >nul 2>&1
        goto :create_db
    )
    goto :db_ready
)

:create_db
echo  [..] Creando base de datos '%DB_NAME%'...
"%MYSQL_EXE%" --user=%DB_USER% --host=127.0.0.1 --port=3306 -e "CREATE DATABASE `%DB_NAME%` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >nul 2>&1

if %errorlevel% neq 0 (
    echo  [ERROR] No se pudo crear la base de datos.
    pause
    exit /b 1
)
echo  [OK] Base de datos creada.

if exist "%SQL_FILE%" (
    echo  [..] Importando datos (esto puede tardar unos minutos)...
    "%MYSQL_EXE%" --user=%DB_USER% --host=127.0.0.1 --port=3306 %DB_NAME% < "%SQL_FILE%" >nul 2>&1
    if %errorlevel% equ 0 (
        echo  [OK] Datos importados correctamente.
    ) else (
        echo  [WARN] Error al importar. Ejecutando migraciones...
        goto :run_migrations
    )
) else (
    echo  [INFO] No se encontro database.sql, ejecutando migraciones...
    goto :run_migrations
)
goto :db_ready

:run_migrations
cd /d "%PROJECT_DIR%"
"%PHP_EXE%" -d error_reporting=22527 artisan migrate --force >nul 2>&1
"%PHP_EXE%" -d error_reporting=22527 artisan db:seed --force >nul 2>&1
echo  [OK] Migraciones ejecutadas.

:db_ready
timeout /t 1 /nobreak >nul

REM ===== PASO 4: Configurar .env =====
cls
echo.
echo  [Paso 4/6] Configurando aplicacion...
echo.

if not exist "%ENV_FILE%" (
    echo  [..] Creando archivo .env...
    (
        echo APP_NAME="CFE PRO"
        echo APP_ENV=local
        echo APP_KEY=
        echo APP_DEBUG=true
        echo APP_URL=http://localhost:8000
        echo LOG_CHANNEL=stack
        echo DB_CONNECTION=mysql
        echo DB_HOST=127.0.0.1
        echo DB_PORT=3306
        echo DB_DATABASE=%DB_NAME%
        echo DB_USERNAME=%DB_USER%
        echo DB_PASSWORD=%DB_PASS%
    ) > "%ENV_FILE%"
    
    cd /d "%PROJECT_DIR%"
    "%PHP_EXE%" -d error_reporting=22527 artisan key:generate --force >nul 2>&1
    echo  [OK] Archivo .env creado y clave generada.
) else (
    echo  [OK] Archivo .env ya existe.
)

REM Verificar vendor
if exist "%PROJECT_DIR%vendor\autoload.php" (
    echo  [OK] Dependencias instaladas.
) else (
    echo  [ERROR] Falta la carpeta vendor/
    echo  Necesita ejecutar: composer install
    pause
    exit /b 1
)

REM Limpiar cache
echo  [..] Limpiando cache...
cd /d "%PROJECT_DIR%"
"%PHP_EXE%" -d error_reporting=22527 artisan config:clear >nul 2>&1
"%PHP_EXE%" -d error_reporting=22527 artisan cache:clear >nul 2>&1
"%PHP_EXE%" -d error_reporting=22527 artisan view:clear >nul 2>&1
echo  [OK] Cache limpiado.

REM Crear directorios
if not exist "%PROJECT_DIR%storage\app\public" mkdir "%PROJECT_DIR%storage\app\public" >nul 2>&1
if not exist "%PROJECT_DIR%storage\framework\cache\data" mkdir "%PROJECT_DIR%storage\framework\cache\data" >nul 2>&1
if not exist "%PROJECT_DIR%storage\framework\sessions" mkdir "%PROJECT_DIR%storage\framework\sessions" >nul 2>&1
if not exist "%PROJECT_DIR%storage\framework\views" mkdir "%PROJECT_DIR%storage\framework\views" >nul 2>&1
if not exist "%PROJECT_DIR%storage\logs" mkdir "%PROJECT_DIR%storage\logs" >nul 2>&1
echo  [OK] Directorios verificados.

timeout /t 1 /nobreak >nul

REM ===== PASO 5: Crear accesos directos =====
cls
echo.
echo  [Paso 5/6] Creando accesos directos...
echo.

REM Crear acceso directo en el Escritorio
powershell -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut([Environment]::GetFolderPath('Desktop') + '\CFE PRO - Facturacion.lnk'); $s.TargetPath = '%PROJECT_DIR%iniciar_servidor.bat'; $s.WorkingDirectory = '%PROJECT_DIR%'; $s.Description = 'Iniciar Sistema de Facturacion CFE PRO'; $s.Save()" >nul 2>&1
echo  [OK] Acceso directo creado en el Escritorio.

REM Crear acceso en Menu Inicio
powershell -Command "$ws = New-Object -ComObject WScript.Shell; $startMenu = [Environment]::GetFolderPath('StartMenu') + '\Programs'; if(!(Test-Path $startMenu)){$startMenu = [Environment]::GetFolderPath('StartMenu')}; $s = $ws.CreateShortcut($startMenu + '\CFE PRO - Facturacion.lnk'); $s.TargetPath = '%PROJECT_DIR%iniciar_servidor.bat'; $s.WorkingDirectory = '%PROJECT_DIR%'; $s.Description = 'Iniciar Sistema de Facturacion CFE PRO'; $s.Save()" >nul 2>&1
echo  [OK] Acceso creado en Menu Inicio.

timeout /t 1 /nobreak >nul

REM ===== PASO 6: COMPLETADO =====
cls
color 0A
echo.
echo  ============================================================
echo.
echo     INSTALACION COMPLETADA EXITOSAMENTE!
echo.
echo  ============================================================
echo.
echo   Para iniciar el sistema:
echo     - Doble click en "iniciar_servidor.bat"
echo     - O use el acceso directo del Escritorio
echo.
echo   Datos de acceso:
echo     URL:      http://127.0.0.1:8000
echo     Usuario:  admin@example.com
echo     Clave:    123456
echo.
echo   Para detener:
echo     - Doble click en "detener_servidor.bat"
echo.
echo  ============================================================
echo.

set /p START_NOW="  Desea iniciar el sistema ahora? (S/N): "
if /i "%START_NOW%"=="S" (
    echo.
    echo  Iniciando sistema...
    start "" "%PROJECT_DIR%iniciar_servidor.bat"
)

echo.
echo  Gracias por instalar CFE PRO!
echo.
pause
