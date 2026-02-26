; ============================================================
; Inno Setup Script - CFE PRO Sistema de Facturacion
; ============================================================
; Para compilar este script necesita Inno Setup 6+
; Descargue de: https://jrsoftware.org/isdl.php
; 
; INSTRUCCIONES:
; 1. Instale Inno Setup
; 2. Abra este archivo .iss con Inno Setup
; 3. Click en Build > Compile
; 4. El instalador .exe se genera en la carpeta Output/
; ============================================================

#define MyAppName "CFE PRO - Sistema de Facturacion"
#define MyAppVersion "1.0.0"
#define MyAppPublisher "Publideas UY"
#define MyAppURL "http://localhost:8000"
#define MyAppExeName "iniciar_servidor.bat"

[Setup]
AppId={{A1B2C3D4-E5F6-7890-ABCD-EF1234567890}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppPublisher={#MyAppPublisher}
AppPublisherURL={#MyAppURL}
DefaultDirName={autopf}\CFE PRO
DefaultGroupName={#MyAppName}
AllowNoIcons=yes
; Output directory relative to this .iss file
OutputDir=..\..\Output
OutputBaseFilename=CFE_PRO_Instalador_v{#MyAppVersion}
SetupIconFile=..\..\public\favicon.ico
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
DisableProgramGroupPage=yes
PrivilegesRequired=admin
; Approximate size in KB
ExtraDiskSpaceRequired=524288000
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible

[Languages]
Name: "spanish"; MessagesFile: "compiler:Languages\Spanish.isl"

[Messages]
spanish.WelcomeLabel1=Bienvenido al Asistente de Instalacion de {#MyAppName}
spanish.WelcomeLabel2=Este asistente instalara {#MyAppName} version {#MyAppVersion} en su computador.%n%nEl sistema incluye:%n  - Servidor PHP%n  - Base de datos MySQL%n  - Sistema de Facturacion Electronica (CFE)%n%nSe recomienda cerrar todas las aplicaciones antes de continuar.
spanish.FinishedLabel=La instalacion de {#MyAppName} se ha completado.%n%nPara iniciar el sistema ejecute "Iniciar CFE PRO" desde el menu Inicio o escritorio.

[Tasks]
Name: "desktopicon"; Description: "Crear acceso directo en el Escritorio"; GroupDescription: "Accesos directos:"; Flags: checked
Name: "startmenu"; Description: "Crear acceso en el Menu Inicio"; GroupDescription: "Accesos directos:"; Flags: checked

[Files]
; Copiar todo el proyecto (excluyendo archivos innecesarios)
Source: "..\..\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs; Excludes: "*.log,Output\*,.git\*,node_modules\*,temp_*.php,test_*.php,check_*.php,fix_*.php,debug_*.php,storage\logs\*.log,storage\framework\cache\data\*,storage\framework\sessions\*,storage\framework\views\*"

[Icons]
Name: "{group}\Iniciar CFE PRO"; Filename: "{app}\iniciar_servidor.bat"; WorkingDir: "{app}"; IconFilename: "{app}\public\favicon.ico"; Comment: "Iniciar el Sistema de Facturacion"
Name: "{group}\Detener CFE PRO"; Filename: "{app}\detener_servidor.bat"; WorkingDir: "{app}"; Comment: "Detener servicios"
Name: "{group}\Desinstalar CFE PRO"; Filename: "{uninstallexe}"
Name: "{autodesktop}\CFE PRO - Facturacion"; Filename: "{app}\iniciar_servidor.bat"; WorkingDir: "{app}"; IconFilename: "{app}\public\favicon.ico"; Tasks: desktopicon

[Run]
; Ejecutar instalador post-copia
Filename: "powershell.exe"; Parameters: "-ExecutionPolicy Bypass -NoProfile -File ""{app}\scripts\installer\install.ps1"" -Silent"; WorkingDir: "{app}"; StatusMsg: "Configurando base de datos y aplicacion..."; Flags: runhidden waituntilterminated
; Preguntar si iniciar el sistema
Filename: "{app}\iniciar_servidor.bat"; Description: "Iniciar CFE PRO ahora"; WorkingDir: "{app}"; Flags: nowait postinstall skipifsilent shellexec

[UninstallRun]
; Detener servicios antes de desinstalar
Filename: "{app}\detener_servidor.bat"; Flags: runhidden waituntilterminated skipifdoesntexist

[UninstallDelete]
Type: filesandordirs; Name: "{app}\storage\logs"
Type: filesandordirs; Name: "{app}\storage\framework\cache"
Type: filesandordirs; Name: "{app}\storage\framework\sessions"
Type: filesandordirs; Name: "{app}\storage\framework\views"

[Code]
// Verificar que no haya otra instancia corriendo
function InitializeSetup(): Boolean;
begin
  Result := True;
  // Podriamos verificar si el puerto 8000 esta en uso
end;
