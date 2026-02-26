@echo off
title CFE PRO - Exportar Base de Datos
color 0E

set "PROJECT_DIR=%~dp0..\.."
set "MYSQLDUMP=%PROJECT_DIR%\xampp\mysql\bin\mysqldump.exe"
set "OUTPUT=%~dp0database.sql"

echo.
echo  ============================================
echo   CFE PRO - Exportar Base de Datos
echo  ============================================
echo.
echo  Exportando base de datos sistema_facturacion_2...
echo.

"%MYSQLDUMP%" --user=root --host=127.0.0.1 --port=3306 --databases sistema_facturacion_2 --add-drop-database --add-drop-table --routines --triggers --single-transaction --set-gtid-purged=OFF > "%OUTPUT%" 2>nul

if %errorlevel% equ 0 (
    echo  [OK] Base de datos exportada a:
    echo       scripts\installer\database.sql
    echo.
    for %%A in ("%OUTPUT%") do echo  Tamano: %%~zA bytes
) else (
    echo  [ERROR] No se pudo exportar la base de datos
    echo  Verifique que MySQL este corriendo.
)

echo.
pause
