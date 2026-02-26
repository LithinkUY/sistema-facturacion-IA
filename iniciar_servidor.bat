@echo off
chcp 65001 >nul 2>&1
title CFE PRO - Sistema de Facturacion
color 0A
cls
echo.
echo  ============================================================
echo    CFE PRO - Sistema de Facturacion
echo    Iniciando servicios...
echo  ============================================================
echo.

set "PROJECT_DIR=%~dp0"
set "PHP_EXE=%PROJECT_DIR%xampp\php\php.exe"
set "MYSQL_DIR=%PROJECT_DIR%xampp\mysql"
set "MYSQLD_EXE=%MYSQL_DIR%\bin\mysqld.exe"
set "MYSQL_EXE=%MYSQL_DIR%\bin\mysql.exe"

echo  [1/3] Verificando MySQL...
"%MYSQL_EXE%" --user=root --host=127.0.0.1 --port=3306 -e "SELECT 1;" >nul 2>&1
if %errorlevel% neq 0 (
    echo       Iniciando MySQL...
    start "" /B "%MYSQLD_EXE%" --basedir="%MYSQL_DIR%" --datadir="%MYSQL_DIR%\data" --port=3306
    timeout /t 5 /nobreak >nul
    echo       MySQL iniciado.
) else (
    echo       MySQL ya esta corriendo.
)

echo  [2/3] Iniciando servidor web en puerto 8000...
echo.
echo  ============================================================
echo   Sistema listo! Abra su navegador en:
echo.
echo     http://127.0.0.1:8000
echo.
echo   Usuario: admin@example.com
echo   Clave:   123456
echo  ============================================================
echo.
echo  [3/3] Abriendo navegador...
start "" "http://127.0.0.1:8000"
echo.
echo  (No cierre esta ventana mientras use el sistema)
echo  (Presione Ctrl+C para detener)
echo.

cd /d "%PROJECT_DIR%"
"%PHP_EXE%" -d error_reporting=22527 artisan serve --host=127.0.0.1 --port=8000
pause
