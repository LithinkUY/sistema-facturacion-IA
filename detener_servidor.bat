@echo off
echo Deteniendo servicios CFE PRO...
echo.

taskkill /F /IM php.exe >nul 2>&1
echo  [OK] PHP detenido.

set "MYSQL_DIR=%~dp0xampp\mysql"
"%MYSQL_DIR%\bin\mysqladmin.exe" --user=root --host=127.0.0.1 shutdown >nul 2>&1
echo  [OK] MySQL detenido.

echo.
echo  Servicios detenidos correctamente.
timeout /t 3
