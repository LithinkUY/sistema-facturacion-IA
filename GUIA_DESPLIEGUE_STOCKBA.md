# 🚀 Guía de Despliegue - stockba.es

## Sistema de Facturación en Hosting PHP con dominio stockba.es

---

## 📋 Requisitos del Hosting

| Requisito | Mínimo | Recomendado |
|-----------|--------|-------------|
| PHP | 8.0+ | 8.1 o 8.2 |
| MySQL | 5.7+ | 8.0 |
| Espacio disco | 500 MB | 2 GB |
| RAM | 512 MB | 1 GB |
| SSL | Sí (Let's Encrypt) | Sí |

### Extensiones PHP necesarias:
- `pdo_mysql`
- `mbstring`
- `openssl`
- `tokenizer`
- `xml`
- `ctype`
- `json`
- `bcmath`
- `gd` o `imagick`
- `zip`
- `curl`
- `fileinfo`

### Hostings recomendados (compatibles):
- **Hostinger** (Business o Premium) — Recomendado
- **Raiola Networks** (hosting España)
- **SiteGround**
- **Contabo VPS** (más control)
- **DigitalOcean** (VPS)

---

## 📦 PASO 1: Preparar el proyecto local

### 1.1 Limpiar archivos temporales
```bash
# Eliminar archivos temp_ de la raíz
del temp_*.php

# Limpiar cachés
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

### 1.2 Exportar la base de datos
```bash
# Desde XAMPP/phpMyAdmin o línea de comandos:
mysqldump -u root -p sistema_facturacion_2 > backup_db_stockba.sql
```

### 1.3 Comprimir el proyecto
Comprimir toda la carpeta del proyecto en un ZIP, **excluyendo**:
- `vendor/` (se instala en el servidor)
- `node_modules/` (si existe)
- `storage/logs/*.log`
- `.env` (se crea en el servidor)
- `backup_old_project/`
- `xampp/`
- Archivos `temp_*.php`

```
📁 proyecto.zip
├── app/
├── bootstrap/
├── config/
├── database/
├── lang/
├── Modules/
├── public/
├── resources/
├── routes/
├── storage/ (sin logs pesados)
├── artisan
├── composer.json
├── composer.lock
└── ...
```

---

## 🌐 PASO 2: Configurar el dominio stockba.es

### 2.1 Comprar/Configurar dominio
1. Ir al registrador de dominios (Namecheap, GoDaddy, Cloudflare, etc.)
2. Registrar `stockba.es` si no lo tenés
3. Apuntar los DNS al hosting:

```
Tipo: A
Host: @
Valor: [IP del hosting]
TTL: 3600

Tipo: A  
Host: www
Valor: [IP del hosting]
TTL: 3600

Tipo: CNAME
Host: www
Valor: stockba.es
TTL: 3600
```

### 2.2 En el panel del hosting
- Agregar dominio `stockba.es` como dominio principal o adicional
- Activar SSL (Let's Encrypt gratuito)
- Esperar propagación DNS (hasta 48h, normalmente 1-4h)

---

## 📤 PASO 3: Subir archivos al servidor

### Opción A: Usando cPanel / File Manager
1. Entrar a cPanel del hosting
2. Ir a **File Manager**
3. Subir `proyecto.zip` a `/home/usuario/`
4. Extraer el ZIP
5. Renombrar la carpeta a `stockba` (o el nombre que prefieras)

### Opción B: Usando FTP (FileZilla)
```
Host: ftp.stockba.es (o la IP del servidor)
Usuario: [usuario FTP del hosting]
Password: [contraseña FTP]
Puerto: 21 (o 22 para SFTP)
```

### Opción C: Usando SSH + Git (VPS)
```bash
ssh usuario@stockba.es
cd /var/www/
git clone https://github.com/LithinkUY/sistema-facturacion-IA.git stockba
cd stockba
```

---

## ⚙️ PASO 4: Configurar el servidor

### 4.1 Document Root → carpeta public/
**IMPORTANTE**: El dominio `stockba.es` debe apuntar a la carpeta `public/` del proyecto.

#### En cPanel:
1. Ir a **Dominios** o **Subdominios**
2. Editar `stockba.es`
3. Cambiar **Document Root** a: `/home/usuario/stockba/public`

#### En VPS (Nginx):
```nginx
server {
    listen 80;
    listen 443 ssl;
    server_name stockba.es www.stockba.es;
    root /var/www/stockba/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/stockba.es/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/stockba.es/privkey.pem;

    # Redirigir HTTP a HTTPS
    if ($scheme != "https") {
        return 301 https://$host$request_uri;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    # Tamaño máximo de upload (para documentos, imágenes)
    client_max_body_size 50M;
}
```

#### En VPS (Apache):
```apache
<VirtualHost *:80>
    ServerName stockba.es
    ServerAlias www.stockba.es
    DocumentRoot /var/www/stockba/public
    
    <Directory /var/www/stockba/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Redirigir a HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName stockba.es
    ServerAlias www.stockba.es
    DocumentRoot /var/www/stockba/public
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/stockba.es/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/stockba.es/privkey.pem
    
    <Directory /var/www/stockba/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 4.2 Verificar .htaccess
El archivo `public/.htaccess` ya viene incluido en Laravel. Asegurate de que `mod_rewrite` esté habilitado.

---

## 🗄️ PASO 5: Crear base de datos MySQL

### En cPanel:
1. Ir a **Bases de datos MySQL**
2. Crear base de datos: `stockba_db` (o el nombre que asigne cPanel)
3. Crear usuario: `stockba_user`
4. Asignar TODOS los privilegios al usuario sobre la base

### En VPS:
```sql
mysql -u root -p
CREATE DATABASE stockba_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'stockba_user'@'localhost' IDENTIFIED BY 'TuPasswordSegura123!';
GRANT ALL PRIVILEGES ON stockba_db.* TO 'stockba_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Importar datos:
```bash
mysql -u stockba_user -p stockba_db < backup_db_stockba.sql
```
O desde phpMyAdmin: Importar → Seleccionar archivo `backup_db_stockba.sql`

---

## 🔧 PASO 6: Instalar dependencias + configurar .env

### 6.1 Acceder por SSH o Terminal del hosting
```bash
cd /home/usuario/stockba    # o /var/www/stockba
```

### 6.2 Instalar Composer (si no está instalado)
```bash
# En hosting compartido:
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# O usar el composer del hosting:
php composer.phar install --optimize-autoloader --no-dev
```

### 6.3 Instalar dependencias
```bash
composer install --optimize-autoloader --no-dev
```

### 6.4 Crear archivo .env
```bash
cp .env.example .env
nano .env   # o vi .env
```

### Contenido del .env para producción:
```env
APP_NAME="StockBA - Sistema de Facturación"
APP_TITLE="StockBA"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://stockba.es

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stockba_db
DB_USERNAME=stockba_user
DB_PASSWORD=TuPasswordSegura123!
DB_PREFIX=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.tuproveedor.com
MAIL_PORT=587
MAIL_USERNAME=info@stockba.es
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@stockba.es
MAIL_FROM_NAME="StockBA"

APP_TIMEZONE=America/Montevideo
APP_LOCALE=es
```

### 6.5 Generar clave de aplicación
```bash
php artisan key:generate
```

---

## 📁 PASO 7: Permisos de carpetas

```bash
# Permisos correctos
chmod -R 755 /var/www/stockba
chmod -R 775 /var/www/stockba/storage
chmod -R 775 /var/www/stockba/bootstrap/cache
chmod -R 775 /var/www/stockba/public/uploads

# Propietario correcto (VPS)
chown -R www-data:www-data /var/www/stockba

# En hosting compartido (cPanel), los permisos suelen ser:
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod -R 775 public/uploads
```

---

## 🚀 PASO 8: Optimizar para producción

```bash
# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar autoloader
composer dump-autoload --optimize

# Crear enlace simbólico de storage
php artisan storage:link
```

---

## ✅ PASO 9: Verificar que funciona

1. Abrir `https://stockba.es` en el navegador
2. Debería aparecer la pantalla de login
3. Ingresar con las credenciales del sistema
4. Verificar:
   - ✅ Login funciona
   - ✅ Dashboard muestra números
   - ✅ Se pueden crear ventas
   - ✅ Se pueden crear cotizaciones
   - ✅ Las imágenes se ven
   - ✅ Los PDF se generan
   - ✅ Los reportes funcionan

---

## 🔒 PASO 10: Seguridad post-despliegue

```bash
# 1. Asegurar que APP_DEBUG está en false
# En .env:
APP_DEBUG=false

# 2. Proteger archivos sensibles
# Agregar al .htaccess principal (public/.htaccess ya está bien)
# En la raíz del proyecto (si es accesible):

# 3. Eliminar archivos de desarrollo
rm -f temp_*.php
rm -f phpunit.xml
rm -rf tests/

# 4. Configurar HTTPS forzado
# Agregar en AppServiceProvider.php boot():
# URL::forceScheme('https');
```

### Forzar HTTPS en Laravel:
Editar `app/Providers/AppServiceProvider.php`:
```php
public function boot()
{
    if (config('app.env') === 'production') {
        \URL::forceScheme('https');
    }
}
```

---

## 🔄 PASO 11: Configurar backups automáticos

### Usando spatie/laravel-backup (ya instalado):
```bash
# Backup manual
php artisan backup:run

# Configurar CRON (en cPanel → Cron Jobs, o en VPS):
# Cada día a las 2:00 AM
0 2 * * * cd /var/www/stockba && php artisan backup:run >> /dev/null 2>&1

# También agregar el scheduler de Laravel:
* * * * * cd /var/www/stockba && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 PASO 12: Actualizar el sistema (futuras versiones)

```bash
# 1. Hacer backup primero
php artisan backup:run

# 2. Subir archivos nuevos (FTP, Git, etc.)
git pull origin master   # si usás Git

# 3. Instalar dependencias nuevas
composer install --optimize-autoloader --no-dev

# 4. Ejecutar migraciones (si hay)
php artisan migrate --force

# 5. Limpiar y re-cachear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🆘 Solución de problemas comunes

| Problema | Solución |
|----------|----------|
| Error 500 al entrar | Verificar permisos de `storage/` y `bootstrap/cache/` |
| Pantalla en blanco | `APP_DEBUG=true` temporalmente para ver el error |
| Error de BD | Verificar credenciales en `.env` |
| CSS/JS no carga | Verificar que document root apunte a `public/` |
| Imágenes no se ven | Ejecutar `php artisan storage:link` |
| Error de composer | Usar `php8.1 composer.phar install` (versión PHP específica) |
| Timeout | En `.htaccess`: `php_value max_execution_time 300` |
| Upload falla | En `.htaccess`: `php_value upload_max_filesize 50M` y `php_value post_max_size 50M` |

---

## 📞 Datos finales

| Campo | Valor |
|-------|-------|
| **URL** | https://stockba.es |
| **API REST** | https://stockba.es/api/v1 |
| **Seguridad** | https://stockba.es/security/scan |
| **Gestión API** | https://stockba.es/api-management |
| **Documentación API** | https://stockba.es/api-management/docs |
| **Dominio** | stockba.es |
| **Framework** | Laravel 9 |
| **PHP** | 8.0+ |
| **DB** | MySQL 5.7+ |
| **Timezone** | America/Montevideo |

---

## 🔌 PASO 13: Configurar API REST

El sistema incluye una API REST completa para conectar con webs y otros sistemas.

### 13.1 Crear tu primera API Key
1. Entrar al sistema → menú lateral **API REST**
2. Click **Nueva API Key**
3. Poner nombre descriptivo (ej: "Web stockba.es", "App Móvil")
4. Seleccionar los permisos necesarios
5. **GUARDAR la API Key y el Secret** — el Secret solo se muestra una vez

### 13.2 Probar la API
```bash
# Test de status (público, sin key)
curl https://stockba.es/api/v1/status

# Listar productos (requiere key)
curl -H "X-API-KEY: sk_tu_key_aqui" https://stockba.es/api/v1/products

# Crear contacto
curl -X POST https://stockba.es/api/v1/contacts \
  -H "X-API-KEY: sk_tu_key_aqui" \
  -H "Content-Type: application/json" \
  -d '{"type":"customer","name":"Cliente Web","email":"cliente@web.com"}'
```

### 13.3 Endpoints disponibles
| Método | Endpoint | Permiso |
|--------|----------|---------|
| GET | /api/v1/status | Público |
| GET/POST/PUT/DELETE | /api/v1/products | products.read/write/delete |
| GET | /api/v1/products/{id}/stock | stock.read |
| GET/POST/PUT/DELETE | /api/v1/contacts | contacts.read/write/delete |
| GET | /api/v1/sells | transactions.read |
| GET | /api/v1/purchases | transactions.read |
| GET | /api/v1/summary | reports.read |
| GET/POST | /api/v1/categories | categories.read/write |
| GET/POST | /api/v1/brands | brands.read/write |
| GET | /api/v1/locations | (cualquier permiso) |

### 13.4 Rate Limiting
La API tiene un límite de **60 peticiones por minuto** por IP.

---

## 🛡️ PASO 14: Ejecutar Escaneo de Seguridad

Una vez desplegado el sistema:
1. Ir al menú lateral → **Seguridad**
2. Click **Iniciar Escaneo**
3. El sistema revisará:
   - Archivos con firmas de malware conocidas
   - Permisos incorrectos en carpetas
   - Integridad de archivos críticos
   - Modificaciones recientes sospechosas
4. Si detecta amenazas, podrás enviar a **cuarentena** los archivos sospechosos
5. Los logs se guardan en `storage/logs/security.log`

**Recomendación**: Ejecutar el escaneo al menos **1 vez por semana** después del despliegue.
