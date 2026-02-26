# ============================================================
# INSTALADOR - Sistema de Facturacion CFE PRO
# ============================================================
# Este script configura e instala el sistema completo
# Incluye: PHP, MySQL, Base de datos, Dependencias
# ============================================================

param(
    [string]$InstallPath = "",
    [switch]$Silent = $false
)

$ErrorActionPreference = "Stop"
$Host.UI.RawUI.WindowTitle = "Instalador - CFE PRO Sistema de Facturacion"

# ---- Colores y funciones auxiliares ----
function Write-Header {
    param([string]$Text)
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host "  $Text" -ForegroundColor White
    Write-Host "============================================================" -ForegroundColor Cyan
}

function Write-Step {
    param([string]$Text)
    Write-Host ""
    Write-Host ">> $Text" -ForegroundColor Yellow
}

function Write-Ok {
    param([string]$Text)
    Write-Host "   [OK] $Text" -ForegroundColor Green
}

function Write-Err {
    param([string]$Text)
    Write-Host "   [ERROR] $Text" -ForegroundColor Red
}

function Write-Info {
    param([string]$Text)
    Write-Host "   [INFO] $Text" -ForegroundColor Gray
}

# ---- Determinar rutas ----
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
# El script esta en scripts/installer/, el proyecto esta 2 niveles arriba
$ProjectRoot = (Resolve-Path (Join-Path $ScriptDir "..\..")).Path

Write-Header "SISTEMA DE FACTURACION CFE PRO"
Write-Host ""
Write-Host "  Bienvenido al instalador del Sistema de Facturacion" -ForegroundColor White
Write-Host "  con soporte CFE (DGI Uruguay)" -ForegroundColor White
Write-Host ""

# ---- Verificar que estamos en el directorio correcto ----
if (-Not (Test-Path (Join-Path $ProjectRoot "artisan"))) {
    Write-Err "No se encontro el archivo 'artisan'. Verifique que el instalador esta dentro del proyecto."
    Read-Host "Presione Enter para salir"
    exit 1
}
Write-Ok "Directorio del proyecto: $ProjectRoot"

# ---- Definir rutas ----
$PhpDir = Join-Path $ProjectRoot "xampp\php"
$PhpExe = Join-Path $PhpDir "php.exe"
$MysqlDir = Join-Path $ProjectRoot "xampp\mysql"
$MysqlBin = Join-Path $MysqlDir "bin"
$MysqldExe = Join-Path $MysqlBin "mysqld.exe"
$MysqlExe = Join-Path $MysqlBin "mysql.exe"
$MysqldumpExe = Join-Path $MysqlBin "mysqldump.exe"
$MysqlDataDir = Join-Path $MysqlDir "data"
$EnvFile = Join-Path $ProjectRoot ".env"
$EnvExample = Join-Path $ProjectRoot ".env.example"

# ============================================================
# PASO 1: Verificar PHP
# ============================================================
Write-Step "Paso 1/7: Verificando PHP..."

if (Test-Path $PhpExe) {
    $phpVersion = & $PhpExe -r "echo PHP_VERSION;" 2>$null
    Write-Ok "PHP encontrado: v$phpVersion (portable en xampp\php)"
} else {
    Write-Err "PHP no encontrado en xampp\php\php.exe"
    Write-Info "El sistema requiere PHP 8.0+ incluido en la carpeta xampp\php"
    Read-Host "Presione Enter para salir"
    exit 1
}

# Verificar extensiones necesarias
Write-Step "Verificando extensiones PHP..."
$requiredExtensions = @("pdo_mysql", "mbstring", "openssl", "tokenizer", "json", "curl", "gd")
$missingExtensions = @()

foreach ($ext in $requiredExtensions) {
    $hasExt = & $PhpExe -r "echo extension_loaded('$ext') ? 'yes' : 'no';" 2>$null
    if ($hasExt -eq "yes") {
        Write-Ok "Extension: $ext"
    } else {
        $missingExtensions += $ext
        Write-Err "Extension faltante: $ext"
    }
}

if ($missingExtensions.Count -gt 0) {
    Write-Host ""
    Write-Host "   Se intentara habilitar las extensiones faltantes en php.ini..." -ForegroundColor Yellow
    $phpIni = Join-Path $PhpDir "php.ini"
    if (Test-Path $phpIni) {
        $iniContent = Get-Content $phpIni -Raw
        foreach ($ext in $missingExtensions) {
            # Buscar la linea comentada y descomentarla
            $iniContent = $iniContent -replace ";extension=$ext", "extension=$ext"
        }
        Set-Content $phpIni $iniContent -Encoding UTF8
        Write-Ok "php.ini actualizado con extensiones habilitadas"
    }
}

# ============================================================
# PASO 2: Verificar/Iniciar MySQL
# ============================================================
Write-Step "Paso 2/7: Verificando MySQL..."

if (Test-Path $MysqldExe) {
    Write-Ok "MySQL encontrado en xampp\mysql\bin"
} else {
    Write-Err "MySQL no encontrado en xampp\mysql\bin\mysqld.exe"
    Read-Host "Presione Enter para salir"
    exit 1
}

# Verificar si MySQL ya esta corriendo
$mysqlRunning = $false
try {
    $testConn = & $MysqlExe --user=root --host=127.0.0.1 --port=3306 -e "SELECT 1;" 2>$null
    if ($LASTEXITCODE -eq 0) {
        $mysqlRunning = $true
        Write-Ok "MySQL ya esta corriendo en puerto 3306"
    }
} catch {
    $mysqlRunning = $false
}

if (-Not $mysqlRunning) {
    Write-Info "Iniciando MySQL..."
    
    # Inicializar data directory si no existe
    if (-Not (Test-Path (Join-Path $MysqlDataDir "mysql"))) {
        Write-Info "Inicializando directorio de datos MySQL..."
        & $MysqldExe --initialize-insecure --basedir="$MysqlDir" --datadir="$MysqlDataDir" 2>$null
    }
    
    # Iniciar MySQL en background
    $mysqlIni = Join-Path $MysqlDir "bin\my.ini"
    if (-Not (Test-Path $mysqlIni)) {
        $mysqlIni = Join-Path $MysqlDir "my.ini"
    }

    $mysqlArgs = @("--basedir=`"$MysqlDir`"", "--datadir=`"$MysqlDataDir`"", "--port=3306")
    Start-Process -FilePath $MysqldExe -ArgumentList $mysqlArgs -WindowStyle Hidden
    
    Write-Info "Esperando que MySQL inicie..."
    $attempts = 0
    $maxAttempts = 30
    while ($attempts -lt $maxAttempts) {
        Start-Sleep -Seconds 1
        try {
            & $MysqlExe --user=root --host=127.0.0.1 --port=3306 -e "SELECT 1;" 2>$null | Out-Null
            if ($LASTEXITCODE -eq 0) {
                $mysqlRunning = $true
                break
            }
        } catch {}
        $attempts++
        Write-Host "." -NoNewline
    }
    Write-Host ""
    
    if ($mysqlRunning) {
        Write-Ok "MySQL iniciado correctamente"
    } else {
        Write-Err "No se pudo iniciar MySQL despues de $maxAttempts segundos"
        Write-Info "Intente iniciar MySQL manualmente desde XAMPP Control Panel"
        Read-Host "Presione Enter para salir"
        exit 1
    }
}

# ============================================================
# PASO 3: Crear Base de Datos
# ============================================================
Write-Step "Paso 3/7: Configurando base de datos..."

$DbName = "sistema_facturacion_2"
$DbUser = "root"
$DbPass = ""

# Verificar si la BD ya existe
$dbExists = & $MysqlExe --user=$DbUser --host=127.0.0.1 --port=3306 -e "SHOW DATABASES LIKE '$DbName';" 2>$null
if ($dbExists -match $DbName) {
    Write-Ok "Base de datos '$DbName' ya existe"
    
    if (-Not $Silent) {
        Write-Host ""
        Write-Host "   La base de datos ya existe. Que desea hacer?" -ForegroundColor Yellow
        Write-Host "   1) Mantener datos actuales (recomendado si ya tiene datos)" -ForegroundColor White
        Write-Host "   2) Reinstalar base de datos (BORRA todos los datos)" -ForegroundColor White
        $dbChoice = Read-Host "   Seleccione (1/2)"
        
        if ($dbChoice -eq "2") {
            Write-Info "Eliminando base de datos existente..."
            & $MysqlExe --user=$DbUser --host=127.0.0.1 --port=3306 -e "DROP DATABASE ``$DbName``;" 2>$null
            $dbExists = $null
        }
    }
}

if (-Not ($dbExists -match $DbName)) {
    Write-Info "Creando base de datos '$DbName'..."
    & $MysqlExe --user=$DbUser --host=127.0.0.1 --port=3306 -e "CREATE DATABASE ``$DbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>$null
    
    if ($LASTEXITCODE -eq 0) {
        Write-Ok "Base de datos creada"
    } else {
        Write-Err "Error al crear la base de datos"
        Read-Host "Presione Enter para salir"
        exit 1
    }
    
    # Importar dump si existe
    $sqlDump = Join-Path $ScriptDir "database.sql"
    if (Test-Path $sqlDump) {
        Write-Info "Importando datos iniciales..."
        Get-Content $sqlDump -Raw | & $MysqlExe --user=$DbUser --host=127.0.0.1 --port=3306 $DbName 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Ok "Datos importados correctamente"
        } else {
            Write-Err "Error al importar datos, se ejecutaran migraciones"
        }
    } else {
        Write-Info "No se encontro database.sql, se ejecutaran migraciones..."
    }
}

# ============================================================
# PASO 4: Configurar archivo .env
# ============================================================
Write-Step "Paso 4/7: Configurando archivo .env..."

if (-Not (Test-Path $EnvFile)) {
    if (Test-Path $EnvExample) {
        Copy-Item $EnvExample $EnvFile
        Write-Info "Archivo .env creado desde .env.example"
    } else {
        # Crear .env desde cero
        $envContent = @"
APP_NAME="CFE PRO"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
LOG_CHANNEL=stack
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DbName
DB_USERNAME=$DbUser
DB_PASSWORD=$DbPass
"@
        Set-Content $EnvFile $envContent -Encoding UTF8
        Write-Info "Archivo .env creado"
    }
} else {
    Write-Ok "Archivo .env ya existe"
}

# Actualizar valores en .env
$envContent = Get-Content $EnvFile -Raw
$envContent = $envContent -replace "DB_DATABASE=.*", "DB_DATABASE=$DbName"
$envContent = $envContent -replace "DB_USERNAME=.*", "DB_USERNAME=$DbUser"
$envContent = $envContent -replace "DB_PASSWORD=.*", "DB_PASSWORD=$DbPass"
$envContent = $envContent -replace "DB_HOST=.*", "DB_HOST=127.0.0.1"
$envContent = $envContent -replace "DB_PORT=.*", "DB_PORT=3306"
Set-Content $EnvFile $envContent -Encoding UTF8
Write-Ok "Archivo .env configurado"

# ============================================================
# PASO 5: Instalar dependencias (Composer)
# ============================================================
Write-Step "Paso 5/7: Verificando dependencias..."

$VendorDir = Join-Path $ProjectRoot "vendor"
if (Test-Path (Join-Path $VendorDir "autoload.php")) {
    Write-Ok "Dependencias ya instaladas (vendor/autoload.php existe)"
} else {
    Write-Info "Instalando dependencias con Composer..."
    
    # Verificar si composer esta disponible
    $composerPhar = Join-Path $ProjectRoot "composer.phar"
    $hasComposer = $false
    
    # Buscar composer global
    try {
        & composer --version 2>$null | Out-Null
        if ($LASTEXITCODE -eq 0) { $hasComposer = $true; $composerCmd = "composer" }
    } catch {}
    
    # Buscar composer.phar en el proyecto
    if (-Not $hasComposer -and (Test-Path $composerPhar)) {
        $hasComposer = $true
        $composerCmd = "$PhpExe $composerPhar"
    }
    
    if ($hasComposer) {
        Set-Location $ProjectRoot
        if ($composerCmd -eq "composer") {
            & composer install --no-dev --optimize-autoloader 2>&1
        } else {
            & $PhpExe $composerPhar install --no-dev --optimize-autoloader 2>&1
        }
        Write-Ok "Dependencias instaladas"
    } else {
        Write-Err "Composer no encontrado. Las dependencias deben estar pre-instaladas."
        Write-Info "Descargue Composer de https://getcomposer.org y ejecute: composer install"
        if (-Not (Test-Path (Join-Path $VendorDir "autoload.php"))) {
            Read-Host "Presione Enter para salir"
            exit 1
        }
    }
}

# ============================================================
# PASO 6: Ejecutar migraciones y configuraciones Laravel
# ============================================================
Write-Step "Paso 6/7: Configurando aplicacion Laravel..."

Set-Location $ProjectRoot

# Generar APP_KEY si no existe
$envContent = Get-Content $EnvFile -Raw
if ($envContent -match "APP_KEY=$" -or $envContent -match "APP_KEY=\s") {
    Write-Info "Generando clave de aplicacion..."
    & $PhpExe -d error_reporting=22527 artisan key:generate --force 2>$null
    Write-Ok "Clave generada"
} else {
    Write-Ok "Clave de aplicacion ya configurada"
}

# Ejecutar migraciones solo si la BD esta vacia
$tableCount = & $MysqlExe --user=$DbUser --host=127.0.0.1 --port=3306 -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DbName';" 2>$null
$tableCount = $tableCount.Trim()

if ($tableCount -eq "0" -or $tableCount -eq "") {
    Write-Info "Ejecutando migraciones de base de datos..."
    & $PhpExe -d error_reporting=22527 artisan migrate --force 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Ok "Migraciones ejecutadas"
    } else {
        Write-Err "Error en migraciones (puede que la BD ya tenga datos importados)"
    }
    
    # Ejecutar seeders
    Write-Info "Cargando datos iniciales..."
    & $PhpExe -d error_reporting=22527 artisan db:seed --force 2>$null
    Write-Ok "Datos iniciales cargados"
} else {
    Write-Ok "Base de datos ya tiene $tableCount tablas - omitiendo migraciones"
}

# Limpiar cache
Write-Info "Limpiando cache..."
& $PhpExe -d error_reporting=22527 artisan config:clear 2>$null
& $PhpExe -d error_reporting=22527 artisan cache:clear 2>$null
& $PhpExe -d error_reporting=22527 artisan view:clear 2>$null
Write-Ok "Cache limpiado"

# Crear link de storage si no existe
if (-Not (Test-Path (Join-Path $ProjectRoot "public\storage"))) {
    & $PhpExe -d error_reporting=22527 artisan storage:link 2>$null
    Write-Ok "Storage link creado"
}

# Crear directorios necesarios
$dirs = @(
    "storage\app\public",
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs",
    "storage\app\public\uploads"
)
foreach ($dir in $dirs) {
    $fullPath = Join-Path $ProjectRoot $dir
    if (-Not (Test-Path $fullPath)) {
        New-Item -ItemType Directory -Path $fullPath -Force | Out-Null
    }
}
Write-Ok "Directorios de almacenamiento verificados"

# ============================================================
# PASO 7: Crear accesos directos y scripts de inicio
# ============================================================
Write-Step "Paso 7/7: Creando accesos directos..."

# Crear iniciar_servidor.bat en la raiz del proyecto
$startScript = @"
@echo off
title CFE PRO - Sistema de Facturacion
color 0A
echo.
echo  ============================================
echo   CFE PRO - Sistema de Facturacion
echo   Iniciando servicios...
echo  ============================================
echo.

REM Definir rutas
set "PROJECT_DIR=%~dp0"
set "PHP_EXE=%PROJECT_DIR%xampp\php\php.exe"
set "MYSQL_DIR=%PROJECT_DIR%xampp\mysql"
set "MYSQLD_EXE=%MYSQL_DIR%\bin\mysqld.exe"
set "MYSQL_EXE=%MYSQL_DIR%\bin\mysql.exe"

REM Iniciar MySQL si no esta corriendo
echo [1/3] Verificando MySQL...
"%MYSQL_EXE%" --user=root --host=127.0.0.1 --port=3306 -e "SELECT 1;" >nul 2>&1
if %errorlevel% neq 0 (
    echo       Iniciando MySQL...
    start "" /B "%MYSQLD_EXE%" --basedir="%MYSQL_DIR%" --datadir="%MYSQL_DIR%\data" --port=3306
    timeout /t 5 /nobreak >nul
    echo       MySQL iniciado.
) else (
    echo       MySQL ya esta corriendo.
)

REM Iniciar servidor PHP
echo [2/3] Iniciando servidor web en puerto 8000...
echo.
echo  ============================================
echo   Sistema listo!
echo   Abra su navegador en:
echo.
echo   http://127.0.0.1:8000
echo.
echo   Usuario: admin@example.com
echo   Clave:   123456
echo  ============================================
echo.
echo  (No cierre esta ventana mientras use el sistema)
echo  (Presione Ctrl+C para detener)
echo.

REM Abrir navegador automaticamente
start "" "http://127.0.0.1:8000"

REM Iniciar PHP server
cd /d "%PROJECT_DIR%"
"%PHP_EXE%" -d error_reporting=22527 artisan serve --host=127.0.0.1 --port=8000

pause
"@

$startScriptPath = Join-Path $ProjectRoot "iniciar_servidor.bat"
Set-Content $startScriptPath $startScript -Encoding ASCII
Write-Ok "Creado: iniciar_servidor.bat"

# Crear detener_servidor.bat
$stopScript = @"
@echo off
echo Deteniendo servicios...
taskkill /F /IM php.exe >nul 2>&1
echo PHP detenido.

REM Detener MySQL
set "MYSQL_DIR=%~dp0xampp\mysql"
"%MYSQL_DIR%\bin\mysqladmin.exe" --user=root --host=127.0.0.1 shutdown >nul 2>&1
echo MySQL detenido.

echo.
echo Servicios detenidos correctamente.
timeout /t 3
"@

$stopScriptPath = Join-Path $ProjectRoot "detener_servidor.bat"
Set-Content $stopScriptPath $stopScript -Encoding ASCII
Write-Ok "Creado: detener_servidor.bat"

# ============================================================
# COMPLETADO
# ============================================================
Write-Header "INSTALACION COMPLETADA"
Write-Host ""
Write-Host "  El sistema ha sido instalado correctamente!" -ForegroundColor Green
Write-Host ""
Write-Host "  Para iniciar el sistema:" -ForegroundColor White
Write-Host "    Ejecute: iniciar_servidor.bat" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Para acceder al sistema:" -ForegroundColor White
Write-Host "    URL:      http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "    Usuario:  admin@example.com" -ForegroundColor Cyan
Write-Host "    Clave:    123456" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Para detener el sistema:" -ForegroundColor White
Write-Host "    Ejecute: detener_servidor.bat" -ForegroundColor Cyan
Write-Host ""

if (-Not $Silent) {
    $startNow = Read-Host "Desea iniciar el sistema ahora? (S/N)"
    if ($startNow -match "^[SsYy]") {
        Write-Info "Iniciando sistema..."
        Start-Process -FilePath (Join-Path $ProjectRoot "iniciar_servidor.bat") -WorkingDirectory $ProjectRoot
    }
}

Write-Host ""
Write-Host "  Gracias por instalar CFE PRO!" -ForegroundColor Green
Write-Host ""
