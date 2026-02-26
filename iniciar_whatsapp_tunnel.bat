@echo off
chcp 65001 >nul
echo ============================================
echo   WhatsApp Tunnel - Configuración Local
echo ============================================
echo.
echo Este script crea un túnel público para conectar
echo WhatsApp Business API con tu servidor local.
echo.
echo REQUISITOS:
echo   1. Servidor Laravel corriendo en puerto 8000
echo   2. Cuenta gratuita en https://ngrok.com
echo   3. Auth token de ngrok configurado
echo.
echo Si no tienes auth token:
echo   1. Registrate en https://dashboard.ngrok.com/signup
echo   2. Copia tu token de https://dashboard.ngrok.com/get-started/your-authtoken
echo   3. Ejecuta: ngrok config add-authtoken TU_TOKEN
echo.
pause

echo.
echo Iniciando túnel ngrok en puerto 8000...
echo (La URL pública aparecerá abajo - úsala como Webhook URL en Meta)
echo.
ngrok http 8000
