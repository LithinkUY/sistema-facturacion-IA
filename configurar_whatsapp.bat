@echo off
chcp 65001 >nul
title Configuración WhatsApp Business API
color 0A

echo ╔══════════════════════════════════════════════════════════════╗
echo ║         CONFIGURACIÓN WhatsApp Business API                  ║
echo ║         Sistema de Facturación - Publideas UY                ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

:: Verificar si ngrok está instalado
where ngrok >nul 2>&1
if errorlevel 1 (
    echo [ERROR] ngrok no está instalado.
    echo Instalando ngrok...
    winget install ngrok.ngrok --accept-source-agreements --accept-package-agreements
    echo.
    echo ngrok instalado. Por favor cierra esta ventana y vuelve a ejecutar este script.
    pause
    exit /b
)

echo [OK] ngrok está instalado
echo.

:: Verificar si ya tiene authtoken configurado
ngrok config check >nul 2>&1
echo.
echo ═══════════════════════════════════════════════════════════════
echo  PASO 1: Configurar Token de ngrok
echo ═══════════════════════════════════════════════════════════════
echo.
echo  Si ya configuraste tu authtoken, presiona ENTER para saltar.
echo  Si NO lo has hecho:
echo    1. Ve a: https://dashboard.ngrok.com/signup
echo    2. Crea una cuenta GRATIS
echo    3. Ve a: https://dashboard.ngrok.com/get-started/your-authtoken
echo    4. Copia tu authtoken
echo.
set /p NGROK_TOKEN=Pega tu authtoken aquí (o ENTER para saltar): 

if not "%NGROK_TOKEN%"=="" (
    echo.
    echo Configurando authtoken...
    ngrok config add-authtoken %NGROK_TOKEN%
    echo [OK] Authtoken configurado correctamente
) else (
    echo [SKIP] Saltando configuración de authtoken
)

echo.
echo ═══════════════════════════════════════════════════════════════
echo  PASO 2: Iniciando túnel ngrok
echo ═══════════════════════════════════════════════════════════════
echo.
echo  Asegúrate de que el servidor Laravel esté corriendo en el puerto 8000
echo  (ejecuta iniciar_servidor.bat en otra ventana)
echo.
echo  El túnel se abrirá y verás una URL pública como:
echo    https://xxxx-xx-xx-xx-xx.ngrok-free.app
echo.
echo  Tu URL de webhook será:
echo    https://xxxx-xx-xx-xx-xx.ngrok-free.app/webhook/whatsapp
echo.
echo  IMPORTANTE: Copia esa URL y configúrala en:
echo    1. Meta Developer Console → tu app → WhatsApp → Configuration → Webhook
echo    2. En tu sistema: http://localhost:8000/whatsapp/settings
echo.
echo ═══════════════════════════════════════════════════════════════
echo  Presiona cualquier tecla para iniciar el túnel...
echo ═══════════════════════════════════════════════════════════════
pause >nul

echo.
echo Iniciando túnel ngrok en puerto 8000...
echo (No cierres esta ventana mientras necesites la conexión)
echo.
ngrok http 8000
