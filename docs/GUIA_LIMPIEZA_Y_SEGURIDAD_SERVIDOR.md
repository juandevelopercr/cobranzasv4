# Guía de Limpieza y Seguridad del Servidor — Consortium

**Fecha:** 30 de mayo de 2026  
**Aplica a:** Servidor de producción Consortium (`144.126.137.131`) y Cobranzas (`209.145.53.12`)  
**Motivo:** Servidores comprometidos — ataque de webshells + inyección SEO spam en `index.php`

---

## Contexto del incidente

Los servidores fueron comprometidos mediante dos tipos de ataque:

1. **Webshells** — archivos PHP maliciosos subidos a `storage/app/public/` que permitían ejecutar comandos remotamente, leer el `.env` y robar credenciales enviándolas por Telegram.
2. **SEO Spam injection** — el archivo `public/index.php` fue modificado para servir contenido spam (farmacias, casinos) a bots de búsqueda (Google, Bing) mientras los usuarios humanos veían el sitio normal. Se crearon sitemaps y miles de URLs falsas para posicionar sitios spam en buscadores.

---

## FASE 1 — Eliminar archivos maliciosos

Conectarse al servidor por SSH y ejecutar:

```bash
cd /home/consorti/public_html/public

# Archivos del ataque SEO spam
rm -f konten.html
rm -f kw.txt
rm -f path.txt

# Sitemaps falsos creados por el atacante
rm -f sitemap-1.xml sitemap-2.xml sitemap-3.xml sitemap-5.xml sitemap-index.xml

# Archivo oculto de persistencia del atacante
rm -f .vanta_notified

# Carpetas ajenas al proyecto inyectadas por el atacante
rm -rf /home/consorti/public_html/public/media
rm -rf /home/consorti/public_html/public/static
```

---

## FASE 2 — Restaurar index.php limpio

El `index.php` original de Laravel pesa ~300 bytes. Si pesa más de 400 bytes está infectado.

**Verificar tamaño:**
```bash
wc -c /home/consorti/public_html/public/index.php
```

**Reemplazar con versión limpia:**
```bash
cat > /home/consorti/public_html/public/index.php << 'EOF'
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenanceFile = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenanceFile;
}

require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
EOF
```

---

## FASE 3 — Restaurar robots.txt

El atacante cambió el `robots.txt` a `Disallow:` vacío (permite que todos los bots indexen todo el sitio). Para un sistema administrativo que no debe aparecer en buscadores:

```bash
cat > /home/consorti/public_html/public/robots.txt << 'EOF'
User-agent: *
Disallow: /
EOF
```

---

## FASE 4 — Crear .htaccess en carpetas de storage

Esto impide que cualquier archivo PHP subido al storage sea ejecutado por el servidor web. Es la protección principal contra el vector de ataque que fue usado.

```bash
# Proteger el storage real
cat > /home/consorti/public_html/storage/app/public/.htaccess << 'EOF'
Options -Indexes -ExecCGI

<FilesMatch "\.(php|php3|php4|php5|php7|phtml|phar|sh|bash|py|pl|cgi|asp|aspx)$">
    Order allow,deny
    Deny from all
</FilesMatch>

php_flag engine off
EOF

# Proteger el symlink public/storage
cat > /home/consorti/public_html/public/storage/.htaccess << 'EOF'
Options -Indexes -ExecCGI

<FilesMatch "\.(php|php3|php4|php5|php7|phtml|phar|sh|bash|py|pl|cgi|asp|aspx)$">
    Order allow,deny
    Deny from all
</FilesMatch>

php_flag engine off
EOF
```

---

## FASE 5 — Aplicar permisos correctos de Laravel

### Tabla de referencia de permisos

| Recurso | Permiso | Razón |
|---|---|---|
| Archivos PHP, blade, config | `644` | Solo el dueño escribe, nadie ejecuta desde web |
| Carpetas normales | `755` | Web server puede entrar pero no escribir |
| `storage/` y `bootstrap/cache/` | `775` | Web server necesita escribir logs y cache |
| Archivos dentro de `storage/` | `664` | Web server puede escribir archivos generados |
| `.env` | `640` | Solo dueño y grupo pueden leerlo |
| `.htaccess` de storage | `644` | Solo lectura |

### Comandos para consortium

```bash
cd /home/consorti/public_html

# Propietario correcto
chown -R consorti:consorti .

# Permisos base
find . -type f -not -path "./vendor/*" -not -path "./node_modules/*" -exec chmod 644 {} \;
find . -type d -not -path "./vendor/*" -not -path "./node_modules/*" -exec chmod 755 {} \;

# storage/ y bootstrap/cache/ escribibles por el servidor web
chmod -R 775 storage bootstrap/cache
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

# .env solo lo puede leer el dueño y el grupo
chmod 640 .env

# Archivos públicos
chmod 644 public/index.php
chmod 644 public/.htaccess
chmod 644 storage/app/public/.htaccess
chmod 644 public/storage/.htaccess

# vendor (solo lectura)
find vendor -type d -exec chmod 755 {} \;
find vendor -type f -exec chmod 644 {} \;
```

---

## FASE 6 — Cambiar todas las credenciales comprometidas

El atacante robó todas las variables del `.env` enviándolas por Telegram. Asumir que TODO está comprometido.

### Clave del usuario Linux

```bash
passwd consorti
```

### Clave de la base de datos

```bash
mysql -u root -p
```

```sql
ALTER USER 'consorti_db'@'localhost' IDENTIFIED BY 'NuevaClaveSegura2026!';
FLUSH PRIVILEGES;
EXIT;
```

### Actualizar .env con nuevas credenciales

```bash
nano /home/consorti/public_html/.env
# Actualizar: DB_PASSWORD, APP_KEY si se rotó, y cualquier API key
```

### Limpiar caché de configuración

```bash
cd /home/consorti/public_html
php artisan config:clear
php artisan cache:clear
php artisan view:clear
# Si usa Octane:
php artisan octane:reload
```

---

## FASE 7 — Buscar más infecciones

```bash
# Webshells en storage
find /home/consorti/public_html/storage/app/public -name "*.php" 2>/dev/null

# index.php modificados en subdirectorios
find /home/consorti/public_html -name "index.php" -size +1k \
  -not -path "*/vendor/*" \
  -not -path "*/node_modules/*" 2>/dev/null

# PHP inyectados recientemente en carpeta public
find /home/consorti/public_html/public -name "*.php" \
  -newer /home/consorti/public_html/composer.json \
  -not -path "*/vendor/*" 2>/dev/null

# Archivos ocultos sospechosos
find /home/consorti/public_html/public -name ".*" 2>/dev/null
```

---

## FASE 8 — Aplicar lo mismo en cobranzas

```bash
# Verificar si index.php de cobranzas está infectado
wc -c /home/cobranza/public_html/public/index.php

# Si está infectado, restaurarlo
cat > /home/cobranza/public_html/public/index.php << 'EOF'
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenanceFile = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenanceFile;
}

require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
EOF

# .htaccess de storage en cobranzas
cat > /home/cobranza/public_html/storage/app/public/.htaccess << 'EOF'
Options -Indexes -ExecCGI

<FilesMatch "\.(php|php3|php4|php5|php7|phtml|phar|sh|bash|py|pl|cgi|asp|aspx)$">
    Order allow,deny
    Deny from all
</FilesMatch>

php_flag engine off
EOF

cat > /home/cobranza/public_html/public/storage/.htaccess << 'EOF'
Options -Indexes -ExecCGI

<FilesMatch "\.(php|php3|php4|php5|php7|phtml|phar|sh|bash|py|pl|cgi|asp|aspx)$">
    Order allow,deny
    Deny from all
</FilesMatch>

php_flag engine off
EOF

# Permisos cobranzas
cd /home/cobranza/public_html
chown -R cobranza:cobranza .
find . -type f -not -path "./vendor/*" -not -path "./node_modules/*" -exec chmod 644 {} \;
find . -type d -not -path "./vendor/*" -not -path "./node_modules/*" -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
find storage -type f -exec chmod 664 {} \;
chmod 640 .env
chmod 644 storage/app/public/.htaccess
chmod 644 public/storage/.htaccess
```

---

## FASE 9 — Monitoreo automático (cron)

Agregar al crontab de root para recibir alertas si aparece algún PHP en storage:

```bash
crontab -e
```

Agregar al final:

```bash
# Alerta cada hora si aparece PHP en storage
0 * * * * PHP_FILES=$(find /home/consorti/public_html/storage/app/public /home/consorti/public_html/public/storage -name "*.php" 2>/dev/null); if [ -n "$PHP_FILES" ]; then echo "$PHP_FILES" | mail -s "ALERTA: PHP en storage de Consortium" caceresvega@gmail.com; fi

# Lo mismo para cobranzas
0 * * * * PHP_FILES=$(find /home/cobranza/public_html/storage/app/public /home/cobranza/public_html/public/storage -name "*.php" 2>/dev/null); if [ -n "$PHP_FILES" ]; then echo "$PHP_FILES" | mail -s "ALERTA: PHP en storage de Cobranzas" caceresvega@gmail.com; fi
```

---

## FASE 10 — Limpiar Google Search Console

Si los dominios están en Google Search Console:

1. Entrar a [search.google.com/search-console](https://search.google.com/search-console)
2. Seleccionar el dominio de consortium
3. Ir a **Eliminación de URLs** → solicitar eliminación temporal de URLs spam
4. Ir a **Inspección de URL** en el dominio raíz → pedir re-indexación
5. Repetir para el dominio de cobranzas

---

## Resumen de archivos eliminados

| Archivo / Carpeta | Qué era |
|---|---|
| `public/konten.html` | Plantilla HTML con contenido spam (522 KB) |
| `public/kw.txt` | Lista de keywords spam para posicionar (468 KB) |
| `public/path.txt` | Rutas de páginas spam generadas |
| `public/sitemap-*.xml` | Sitemaps falsos para indexación en Google |
| `public/sitemap-index.xml` | Índice de sitemaps spam |
| `public/.vanta_notified` | Archivo de persistencia/marca del atacante |
| `public/media/` | Carpeta inyectada, no era del proyecto |
| `public/static/` | Carpeta inyectada, no era del proyecto |
| `public/index.php` | Infectado con inyector SEO (2.34 KB vs 300 bytes original) |
| `public/robots.txt` | Modificado para permitir indexación total |

---

## Correcciones implementadas en el código (aplicadas vía git)

Estas correcciones ya están en los repositorios `cobranzasv4` y `consortiumv4` en las ramas `main` y `consortium_test`:

| Corrección | Archivos afectados |
|---|---|
| Regla `NoExecutableFile` en todos los uploads | `CasoDocumentManager`, `MovimientoDocumentManager`, `DocumentsManager` |
| Autenticación requerida en endpoints de API | `routes/api.php` — `/api/user-roles` y `/api/user-assignments` |
| `basename()` en descargas de reportes | 5 controllers — `ReportCaso`, `ReportInvoice`, `ReportMovimiento`, `ReportProforma`, `ReportTransaction` |
