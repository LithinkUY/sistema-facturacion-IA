@echo off
chcp 65001 >nul 2>&1
title CFE PRO - Instalador
color 0B
cls
echo.
echo  ============================================================
echo    CFE PRO - Sistema de Facturacion
echo    Instalador v1.0
echo  ============================================================
echo.
echo  Este instalador extraera y configurara el sistema.
echo.

REM Verificar que el ZIP existe
if not exist "%~dp0CFE_PRO_v1.0.zip" (
    echo  [ERROR] No se encontro CFE_PRO_v1.0.zip
    echo  Asegurese de que este archivo esta junto al instalador.
    pause
    exit /b 1
)

set /p INSTALL_DIR="  Ruta de instalacion [C:\CFE_PRO]: "
if "%INSTALL_DIR%"=="" set "INSTALL_DIR=C:\CFE_PRO"

echo.
echo  [..] Extrayendo archivos en: %INSTALL_DIR%
echo       Esto puede tardar unos minutos...

REM Crear directorio
if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"

REM Extraer ZIP usando PowerShell
powershell -Command "Add-Type -AssemblyName System.IO.Compression.FileSystem; [System.IO.Compression.ZipFile]::ExtractToDirectory('%~dp0CFE_PRO_v1.0.zip', '%INSTALL_DIR%')" 2>nul

if %errorlevel% neq 0 (
    echo  [ERROR] Error al extraer archivos.
    echo  Intente extraer manualmente el ZIP.
    pause
    exit /b 1
)

echo  [OK] Archivos extraidos.
echo.
echo  [..] Ejecutando configuracion...

REM Lanzar instalador del proyecto
if exist "%INSTALL_DIR%\instalar.bat" (
    cd /d "%INSTALL_DIR%"
    call "%INSTALL_DIR%\instalar.bat"
) else (
    echo  [OK] Archivos extraidos en: %INSTALL_DIR%
    echo  Ejecute instalar.bat para completar la configuracion.
)

pause
