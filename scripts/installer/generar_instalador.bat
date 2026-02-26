@echo off
chcp 65001 >nul 2>&1
title CFE PRO - Generar Instalador
color 0B
cls
echo.
echo  ============================================================
echo    CFE PRO - Generador de Instalador Distribuible
echo  ============================================================
echo.
echo  Este script creara los archivos necesarios para instalar
echo  el sistema en otra computadora.
echo.
echo  Se generara:
echo    - CFE_PRO_v1.0.zip        (proyecto comprimido)
echo    - INSTALAR_CFE_PRO.bat     (instalador)
echo.
echo  Los archivos se guardaran en la carpeta "Output".
echo.
pause

cd /d "%~dp0"
powershell -ExecutionPolicy Bypass -File "crear_exe.ps1"
