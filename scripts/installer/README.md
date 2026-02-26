# CFE PRO - Sistema de Facturación - Guía de Instalación

## 📋 Contenido

El sistema incluye todo lo necesario para funcionar:
- **PHP 8.4** (portable, incluido en `xampp/php/`)
- **MySQL/MariaDB** (portable, incluido en `xampp/mysql/`)
- **Laravel 9** (framework web)
- **Base de datos** pre-configurada (en `scripts/installer/database.sql`)

---

## 🚀 Instalación Rápida (Método 1)

### Doble click en `instalar.bat`

1. Copie toda la carpeta del proyecto a la PC destino
2. Haga doble click en **`instalar.bat`** en la raíz del proyecto
3. Siga las instrucciones en pantalla
4. Al finalizar, el sistema se inicia automáticamente en `http://127.0.0.1:8000`

### Credenciales por defecto:
- **URL:** http://127.0.0.1:8000
- **Usuario:** admin@example.com
- **Contraseña:** 123456

---

## 💿 Instalación con EXE (Método 2 - Profesional)

### Requisitos para CREAR el instalador:
1. Instale [Inno Setup 6+](https://jrsoftware.org/isdl.php)
2. Abra `scripts/installer/installer.iss` con Inno Setup
3. Click en **Build > Compile**
4. El archivo `CFE_PRO_Instalador_v1.0.0.exe` se genera en la carpeta `Output/`

### Para USAR el instalador EXE:
1. Ejecute `CFE_PRO_Instalador_v1.0.0.exe`
2. Siga el asistente (Siguiente, Siguiente, Instalar)
3. El sistema se configura automáticamente
4. Use el acceso directo del escritorio para iniciar

---

## 🔧 Uso Diario

### Iniciar el sistema:
```
Doble click en: iniciar_servidor.bat
```
Esto inicia MySQL + PHP y abre el navegador automáticamente.

### Detener el sistema:
```
Doble click en: detener_servidor.bat
```

---

## 📦 Preparar para Distribución

### Actualizar la base de datos del instalador:
Cuando haga cambios en la base de datos y quiera que el instalador incluya los datos actualizados:

1. Asegúrese de que MySQL esté corriendo
2. Ejecute `scripts/installer/exportar_db.bat`
3. Esto actualiza `scripts/installer/database.sql`

### Crear paquete portable (sin Inno Setup):
1. Copie toda la carpeta del proyecto
2. Elimine archivos innecesarios:
   - `temp_*.php`, `test_*.php`, `fix_*.php`, `debug_*.php`
   - `storage/logs/*.log`
   - `.git/` (si existe)
   - `node_modules/` (si existe)
3. Comprima en ZIP
4. El usuario destino descomprime y ejecuta `instalar.bat`

---

## 🗂️ Estructura de Archivos del Instalador

```
proyecto/
├── instalar.bat              ← Punto de entrada (doble click)
├── iniciar_servidor.bat      ← Iniciar sistema
├── detener_servidor.bat      ← Detener sistema
├── scripts/
│   └── installer/
│       ├── install.ps1       ← Script principal de instalación
│       ├── installer.iss     ← Script Inno Setup (para EXE)
│       ├── exportar_db.bat   ← Exportar BD actual
│       ├── database.sql      ← Dump de la BD
│       └── README.md         ← Este archivo
├── xampp/
│   ├── php/                  ← PHP portable
│   └── mysql/                ← MySQL portable
├── app/                      ← Código Laravel
├── public/                   ← Archivos web públicos
├── vendor/                   ← Dependencias PHP
└── ...
```

---

## ⚠️ Notas Importantes

- **Puerto 8000:** El sistema usa el puerto 8000. Si está ocupado, modifique `iniciar_servidor.bat`.
- **Puerto 3306:** MySQL usa el puerto 3306. Si ya tiene MySQL instalado, puede haber conflicto.
- **Firewall:** Windows puede pedir permiso para PHP y MySQL. Debe permitirlos.
- **Antivirus:** Algunos antivirus pueden bloquear `mysqld.exe` o `php.exe`. Agregue excepciones si es necesario.
- **Windows 10/11:** El sistema está optimizado para Windows 10 y 11 de 64 bits.

---

## 🔄 Actualización

Para actualizar un sistema ya instalado:
1. Detenga el sistema (`detener_servidor.bat`)
2. Reemplace los archivos del proyecto (excepto `.env` y `storage/`)
3. Ejecute `instalar.bat` - detectará que la BD ya existe y preguntará qué hacer
4. Inicie el sistema (`iniciar_servidor.bat`)

---

## 🆘 Solución de Problemas

| Problema | Solución |
|----------|----------|
| "Puerto 8000 en uso" | Cierre otra instancia o cambie el puerto en `iniciar_servidor.bat` |
| "MySQL no inicia" | Verifique que el puerto 3306 no esté ocupado (`netstat -an \| findstr 3306`) |
| Página en blanco | Verifique `storage/logs/laravel.log` para errores |
| "Acceso denegado" | Ejecute `instalar.bat` como Administrador |
