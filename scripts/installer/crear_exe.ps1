# ============================================================
# CREAR EXE AUTOEXTRAIBLE - CFE PRO
# ============================================================
# Este script crea un archivo .exe que contiene todo el proyecto
# y se autoextrae + ejecuta el instalador automaticamente.
#
# Usar: Click derecho > "Ejecutar con PowerShell"
#   O:  powershell -ExecutionPolicy Bypass -File crear_exe.ps1
# ============================================================

$ErrorActionPreference = "Stop"

$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$OutputDir = Join-Path $ProjectRoot "Output"
$TempDir = Join-Path $env:TEMP "cfepro_build_$(Get-Date -Format 'yyyyMMddHHmmss')"
$ZipFile = Join-Path $TempDir "cfepro.zip"
$ExeFile = Join-Path $OutputDir "CFE_PRO_Instalador_v1.0.exe"

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  Creador de EXE - CFE PRO Sistema de Facturacion" -ForegroundColor White
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# Crear directorios
if (!(Test-Path $OutputDir)) { New-Item -ItemType Directory -Path $OutputDir -Force | Out-Null }
if (!(Test-Path $TempDir)) { New-Item -ItemType Directory -Path $TempDir -Force | Out-Null }

# Primero exportar la base de datos actualizada
Write-Host ">> Exportando base de datos actual..." -ForegroundColor Yellow
$mysqldump = Join-Path $ProjectRoot "xampp\mysql\bin\mysqldump.exe"
$sqlFile = Join-Path $ProjectRoot "scripts\installer\database.sql"
if (Test-Path $mysqldump) {
    try {
        & $mysqldump --user=root --host=127.0.0.1 --port=3306 sistema_facturacion_2 --add-drop-table --routines --triggers --single-transaction 2>$null | Out-File $sqlFile -Encoding UTF8
        Write-Host "   [OK] Base de datos exportada" -ForegroundColor Green
    } catch {
        Write-Host "   [WARN] No se pudo exportar BD (usando la existente)" -ForegroundColor Yellow
    }
} else {
    Write-Host "   [WARN] mysqldump no encontrado, usando SQL existente" -ForegroundColor Yellow
}

# Definir exclusiones
$excludeFiles = @(
    "temp_*.php", "test_*.php", "check_*.php", "fix_*.php", "debug_*.php",
    "*.log", ".git", "node_modules", "Output",
    "backup_old_project", ".env"
)

Write-Host ">> Preparando archivos para compresion..." -ForegroundColor Yellow

# Copiar proyecto a temp (excluyendo archivos innecesarios)
$tempProject = Join-Path $TempDir "CFE_PRO"
New-Item -ItemType Directory -Path $tempProject -Force | Out-Null

# Usar robocopy para copiar excluyendo directorios grandes
$excludeDirs = @(".git", "node_modules", "Output", "backup_old_project", "storage\logs", "storage\framework\cache\data", "storage\framework\sessions", "storage\framework\views")
$excludePatterns = @("temp_*.php", "test_*.php", "check_*.php", "fix_*.php", "debug_*.php", "*.log", ".env")

$robocopyArgs = @(
    "`"$ProjectRoot`"",
    "`"$tempProject`"",
    "/E", "/NFL", "/NDL", "/NJH", "/NJS", "/NC", "/NS", "/NP"
)

# Build exclude dir args
foreach ($dir in $excludeDirs) {
    $robocopyArgs += "/XD"
    $robocopyArgs += "`"$dir`""
}

# Build exclude file args
foreach ($pattern in $excludePatterns) {
    $robocopyArgs += "/XF"
    $robocopyArgs += "`"$pattern`""
}

Write-Host "   Copiando archivos (esto puede tardar unos minutos)..." -ForegroundColor Gray

# Use robocopy
$robocopyCmd = "robocopy `"$ProjectRoot`" `"$tempProject`" /E /NFL /NDL /NJH /NJS /NC /NS /NP"
foreach ($dir in $excludeDirs) {
    $robocopyCmd += " /XD `"$dir`""
}
foreach ($pattern in $excludePatterns) {
    $robocopyCmd += " /XF `"$pattern`""
}

cmd /c $robocopyCmd 2>$null | Out-Null

# Crear directorios vacios necesarios
$emptyDirs = @(
    "storage\logs",
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\app\public"
)
foreach ($dir in $emptyDirs) {
    $fullPath = Join-Path $tempProject $dir
    if (!(Test-Path $fullPath)) {
        New-Item -ItemType Directory -Path $fullPath -Force | Out-Null
    }
    # Create .gitkeep to preserve directory
    "" | Out-File (Join-Path $fullPath ".gitkeep") -Encoding UTF8
}

# Crear .env.example en la copia
$envExample = @"
APP_NAME="CFE PRO"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
LOG_CHANNEL=stack
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_facturacion_2
DB_USERNAME=root
DB_PASSWORD=
"@
$envExample | Out-File (Join-Path $tempProject ".env.example") -Encoding UTF8

$fileCount = (Get-ChildItem $tempProject -Recurse -File).Count
Write-Host "   [OK] $fileCount archivos preparados" -ForegroundColor Green

# Crear ZIP
Write-Host ">> Comprimiendo proyecto..." -ForegroundColor Yellow
Write-Host "   Esto puede tardar varios minutos..." -ForegroundColor Gray

if (Test-Path $ZipFile) { Remove-Item $ZipFile -Force }

# Use .NET compression for better compatibility
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($tempProject, $ZipFile, [System.IO.Compression.CompressionLevel]::Optimal, $false)

$zipSize = [math]::Round((Get-Item $ZipFile).Length / 1MB, 1)
Write-Host "   [OK] ZIP creado: $zipSize MB" -ForegroundColor Green

# Crear EXE autoextraible con PowerShell
Write-Host ">> Generando EXE autoextraible..." -ForegroundColor Yellow

# El EXE es un script PowerShell empotrado + datos ZIP
# Cuando se ejecuta, extrae el ZIP y lanza instalar.bat

$extractorScript = @'
# CFE PRO - Autoextractor
$ErrorActionPreference = "Stop"
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.Windows.Forms

[System.Windows.Forms.MessageBox]::Show(
    "Se instalara CFE PRO - Sistema de Facturacion.`n`nSeleccione la carpeta donde desea instalar el sistema.",
    "CFE PRO - Instalador",
    [System.Windows.Forms.MessageBoxButtons]::OK,
    [System.Windows.Forms.MessageBoxIcon]::Information
) | Out-Null

$fbd = New-Object System.Windows.Forms.FolderBrowserDialog
$fbd.Description = "Seleccione la carpeta de instalacion para CFE PRO"
$fbd.SelectedPath = "C:\CFE_PRO"
$fbd.ShowNewFolderButton = $true

if ($fbd.ShowDialog() -ne "OK") {
    [System.Windows.Forms.MessageBox]::Show("Instalacion cancelada.", "CFE PRO") | Out-Null
    exit
}

$installPath = $fbd.SelectedPath

Write-Host "Extrayendo archivos en: $installPath"
Write-Host "Por favor espere..."

# Leer el ZIP empotrado en este EXE
$selfPath = $MyInvocation.MyCommand.Path
$bytes = [System.IO.File]::ReadAllBytes($selfPath)

# Buscar el marcador de inicio del ZIP (PK signature)
$zipStart = -1
for ($i = $bytes.Length - 22; $i -ge 0; $i--) {
    if ($bytes[$i] -eq 0x50 -and $bytes[$i+1] -eq 0x4B -and $bytes[$i+2] -eq 0x05 -and $bytes[$i+3] -eq 0x06) {
        # Found End of Central Directory
        $cdOffset = [BitConverter]::ToUInt32($bytes, $i + 16)
        # Now find the actual start of ZIP
        for ($j = 0; $j -lt $bytes.Length - 4; $j++) {
            if ($bytes[$j] -eq 0x50 -and $bytes[$j+1] -eq 0x4B -and ($bytes[$j+2] -eq 0x03 -or $bytes[$j+2] -eq 0x05) -and ($bytes[$j+3] -eq 0x04 -or $bytes[$j+3] -eq 0x06)) {
                $zipStart = $j
                break
            }
        }
        break
    }
}

if ($zipStart -lt 0) {
    [System.Windows.Forms.MessageBox]::Show("Error: No se encontro el archivo de datos.", "CFE PRO - Error") | Out-Null
    exit 1
}

$tempZip = Join-Path $env:TEMP "cfepro_install.zip"
$zipBytes = New-Object byte[] ($bytes.Length - $zipStart)
[Array]::Copy($bytes, $zipStart, $zipBytes, 0, $zipBytes.Length)
[System.IO.File]::WriteAllBytes($tempZip, $zipBytes)

# Extraer
if (!(Test-Path $installPath)) { New-Item -ItemType Directory -Path $installPath -Force | Out-Null }
[System.IO.Compression.ZipFile]::ExtractToDirectory($tempZip, $installPath)

Remove-Item $tempZip -Force -ErrorAction SilentlyContinue

# Lanzar instalador
$installer = Join-Path $installPath "instalar.bat"
if (Test-Path $installer) {
    Start-Process -FilePath $installer -WorkingDirectory $installPath -Wait
} else {
    [System.Windows.Forms.MessageBox]::Show(
        "Archivos extraidos en:`n$installPath`n`nEjecute 'instalar.bat' para completar la instalacion.",
        "CFE PRO - Extraccion completa",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Information
    ) | Out-Null
}
'@

# Crear el EXE: PowerShell script como BAT + ZIP adjunto
$batLauncher = @"
@echo off
title CFE PRO - Instalador
echo.
echo  Iniciando instalador CFE PRO...
echo  Por favor espere...
echo.
powershell -ExecutionPolicy Bypass -Command "& { `$script = Get-Content '%~f0' -Raw; `$psStart = `$script.IndexOf('# CFE PRO - Autoextractor'); if(`$psStart -gt 0) { `$psCode = `$script.Substring(`$psStart); Invoke-Expression `$psCode } }"
exit /b
$extractorScript
"@

# Metodo alternativo mas simple: crear un BAT que extrae un ZIP adjunto
# Usaremos el metodo de ZIP separado + BAT launcher

# Copiar ZIP a Output
$outputZip = Join-Path $OutputDir "CFE_PRO_v1.0.zip"
Copy-Item $ZipFile $outputZip -Force

# Crear BAT instalador que acompana al ZIP
$batInstaller = @"
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
"@

$batInstallerPath = Join-Path $OutputDir "INSTALAR_CFE_PRO.bat"
$batInstaller | Out-File $batInstallerPath -Encoding ASCII

Write-Host "   [OK] Archivos de instalacion generados" -ForegroundColor Green

# Limpieza
Write-Host ">> Limpiando archivos temporales..." -ForegroundColor Yellow
Remove-Item $TempDir -Recurse -Force -ErrorAction SilentlyContinue
Write-Host "   [OK] Limpieza completada" -ForegroundColor Green

# Resumen
Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host "  GENERACION COMPLETADA" -ForegroundColor White
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Archivos generados en: $OutputDir" -ForegroundColor White
Write-Host ""
$zipSizeOutput = [math]::Round((Get-Item $outputZip).Length / 1MB, 1)
Write-Host "  1. CFE_PRO_v1.0.zip       ($zipSizeOutput MB) - Proyecto comprimido" -ForegroundColor Cyan
Write-Host "  2. INSTALAR_CFE_PRO.bat    - Instalador (poner junto al ZIP)" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Para distribuir:" -ForegroundColor Yellow
Write-Host "    Copie AMBOS archivos a la PC destino y ejecute INSTALAR_CFE_PRO.bat" -ForegroundColor White
Write-Host ""
Write-Host "  Opcion alternativa:" -ForegroundColor Yellow
Write-Host "    Envie solo el ZIP, el usuario lo descomprime y ejecuta instalar.bat" -ForegroundColor White
Write-Host ""

Read-Host "Presione Enter para abrir la carpeta Output"
Start-Process explorer.exe $OutputDir
