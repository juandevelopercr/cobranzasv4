# Guía de Limpieza y Seguridad del Servidor — Cobranzas

**Fecha:** 30 de mayo de 2026  
**Aplica a:** Servidor de producción Cobranzas (`209.145.53.12`) y Consortium (`144.126.137.131`)  
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
cd /home/cobranza/public_html/public

rm -f konten.html
rm -f kw.txt
rm -f path.txt
rm -f sitemap-1.xml sitemap-2.xml sitemap-3.xml sitemap-5.xml sitemap-index.xml
rm -f .vanta_notified
rm -rf /home/cobranza/public_html/public/media
rm -rf /home/cobranza/public_html/public/static
```

---

## FASE 2 — Restaurar index.php limpio

Verificar tamaño — si pesa más de 400 bytes está infectado:

```bash
wc -c /home/cobranza/public_html/public/index.php
```

Abrir el archivo con el editor y reemplazar **todo el contenido** con exactamente esto:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenanceFile = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenanceFile;
}

require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
```

---

## FASE 3 — Restaurar robots.txt

El atacante cambió el `robots.txt` a `Disallow:` vacío para que Google indexara sus páginas spam. El archivo debe contener exactamente esto:

```
User-agent: *
Disallow: /
```

---

## FASE 4 — Crear .htaccess en carpetas de storage

Impide que cualquier archivo PHP subido al storage sea ejecutado por el servidor web. Crear **dos archivos** con el mismo contenido:

**Archivo 1:** `/home/cobranza/public_html/storage/app/public/.htaccess`  
**Archivo 2:** `/home/cobranza/public_html/public/storage/.htaccess`

Contenido exacto de ambos archivos:

```apache
Options -Indexes -ExecCGI

<FilesMatch "\.(php|php3|php4|php5|php7|phtml|phar|sh|bash|py|pl|cgi|asp|aspx)$">
    Order allow,deny
    Deny from all
</FilesMatch>

php_flag engine off
```

Permisos de los archivos `.htaccess`:

```bash
chmod 644 /home/cobranza/public_html/storage/app/public/.htaccess
chmod 644 /home/cobranza/public_html/public/storage/.htaccess
```

---

## FASE 5 — Aplicar permisos correctos de Laravel

### Tabla de referencia

| Recurso                         | Permiso | Razón                                          |
| ------------------------------- | ------- | ---------------------------------------------- |
| Archivos PHP, blade, config     | `644`   | Solo el dueño escribe, nadie ejecuta desde web |
| Carpetas normales               | `755`   | Web server puede entrar pero no escribir       |
| `storage/` y `bootstrap/cache/` | `775`   | Web server necesita escribir logs y cache      |
| Archivos dentro de `storage/`   | `664`   | Web server puede escribir archivos generados   |
| `.env`                          | `640`   | Solo dueño y grupo pueden leerlo               |
| `.htaccess` de storage          | `644`   | Solo lectura                                   |

### Comandos para cobranzas

```bash
cd /home/cobranza/public_html

chown -R cobranza:cobranza .

find . -type f -not -path "./vendor/*" -not -path "./node_modules/*" -exec chmod 644 {} \;
find . -type d -not -path "./vendor/*" -not -path "./node_modules/*" -exec chmod 755 {} \;

chmod -R 775 storage bootstrap/cache
find storage -type f -print0 | xargs -0 chmod 664
find bootstrap/cache -type f -print0 | xargs -0 chmod 664

chmod 640 .env
chmod 644 public/index.php
chmod 644 public/.htaccess
chmod 644 storage/app/public/.htaccess
chmod 644 public/storage/.htaccess

find vendor -type d -exec chmod 755 {} \;
find vendor -type f -exec chmod 644 {} \;
```

---

## FASE 6 — Cambiar todas las credenciales comprometidas

El atacante robó todas las variables del `.env` enviándolas por Telegram. Asumir que TODO está comprometido.

**Clave del usuario Linux:**

```bash
passwd cobranza
```

**Clave de la base de datos:**

```bash
mysql -u root -p
```

```sql
ALTER USER 'cobranza_db'@'%' IDENTIFIED BY 'NuevaClaveSegura2026!';
FLUSH PRIVILEGES;
EXIT;
```

**Actualizar `.env`** — editar el archivo y cambiar `DB_PASSWORD` y cualquier API key robada.

**Limpiar caché:**

```bash
cd /home/cobranza/public_html
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## FASE 7 — Buscar más infecciones

```bash
# Webshells en storage
find /home/cobranza/public_html/storage/app/public -name "*.php" 2>/dev/null

# index.php modificados en subdirectorios
find /home/cobranza/public_html -name "index.php" -size +1k \
  -not -path "*/vendor/*" \
  -not -path "*/node_modules/*" 2>/dev/null

# PHP inyectados recientemente en carpeta public
find /home/cobranza/public_html/public -name "*.php" \
  -newer /home/cobranza/public_html/composer.json \
  -not -path "*/vendor/*" 2>/dev/null

# Archivos ocultos sospechosos
find /home/cobranza/public_html/public -name ".*" 2>/dev/null
```

---

## FASE 8 — Aplicar lo mismo en consortium

**Verificar si index.php está infectado:**

```bash
wc -c /home/consorti/public_html/public/index.php
```

Si pesa más de 400 bytes, reemplazar el contenido con:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenanceFile = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenanceFile;
}

require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
```

Crear los `.htaccess` de storage con el mismo contenido indicado en la FASE 4:

**Archivo 1:** `/home/consorti/public_html/storage/app/public/.htaccess`  
**Archivo 2:** `/home/consorti/public_html/public/storage/.htaccess`

```apache
Options -Indexes -ExecCGI

<FilesMatch "\.(php|php3|php4|php5|php7|phtml|phar|sh|bash|py|pl|cgi|asp|aspx)$">
    Order allow,deny
    Deny from all
</FilesMatch>

php_flag engine off
```

**Permisos consortium:**

```bash
cd /home/consorti/public_html
chown -R consorti:consorti .
find . -type f -not -path "./vendor/*" -not -path "./node_modules/*" -exec chmod 644 {} \;
find . -type d -not -path "./vendor/*" -not -path "./node_modules/*" -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
find storage -type f -exec chmod 664 {} \;
chmod 640 .env
chmod 644 storage/app/public/.htaccess
chmod 644 public/storage/.htaccess
```

---

## FASE 10 — Protección adicional en uploads de documentos

En el commit `e980c5f9c2c751c4d04aa52d737244faeaa8a6c4` se agregó una validación de seguridad adicional para los uploads de documentos en el código de Laravel.

Cambios principales:

- Se creó la regla `App\Rules\NoExecutableFile`.
- Se aplicó en los componentes Livewire que gestionan documentos:
  - `app/Livewire/Casos/CasoDocumentManager.php`
  - `app/Livewire/Movimientos/MovimientoDocumentManager.php`
  - `app/Livewire/Transactions/DocumentsManager.php`
- La regla bloquea extensiones peligrosas como `php`, `sh`, `py`, `exe`, `bat`, `cmd`, `com`, `cgi`, `asp`, `aspx`, `jsp`, entre otras.
- También bloquea tipos MIME sospechosos como `application/x-php`, `text/x-php`, `application/x-executable`, `application/x-shellscript`, `text/x-shellscript`, `application/x-sh`.

Recomendación para revisión de `erpv4`:

- Buscar componentes de subida de archivos similares o servicios que acepten uploads de documentos.
- Verificar si existen reglas de validación equivalentes y, si no, aplicar la misma lógica de `NoExecutableFile`.
- Evaluar cuidadosamente antes de implementar para no romper el comportamiento existente.

> Nota: en el workspace actual no se encontró otra copia de `docs/GUIA_LIMPIEZA_Y_SEGURIDAD_SERVIDOR.md` en `/dev/consortiumv4`.

---

## FASE 9 — Monitoreo automático (cron)

Ejecutar `crontab -e` como root y agregar al final:

```
0 * * * * PHP_FILES=$(find /home/cobranza/public_html/storage/app/public /home/cobranza/public_html/public/storage -name "*.php" 2>/dev/null); if [ -n "$PHP_FILES" ]; then echo "$PHP_FILES" | mail -s "ALERTA: PHP en storage de Cobranzas" caceresvega@gmail.com; fi
0 * * * * PHP_FILES=$(find /home/consorti/public_html/storage/app/public /home/consorti/public_html/public/storage -name "*.php" 2>/dev/null); if [ -n "$PHP_FILES" ]; then echo "$PHP_FILES" | mail -s "ALERTA: PHP en storage de Consortium" caceresvega@gmail.com; fi
```

---

## FASE 10 — Limpiar Google Search Console

1. Entrar a [search.google.com/search-console](https://search.google.com/search-console)
2. Seleccionar el dominio de cobranzas
3. Ir a **Eliminación de URLs** → solicitar eliminación temporal de URLs spam
4. Ir a **Inspección de URL** en el dominio raíz → pedir re-indexación
5. Repetir para el dominio de consortium

---

## Resumen de archivos eliminados

| Archivo / Carpeta          | Qué era                                                     |
| -------------------------- | ----------------------------------------------------------- |
| `public/konten.html`       | Plantilla HTML con contenido spam (522 KB)                  |
| `public/kw.txt`            | Lista de keywords spam (468 KB)                             |
| `public/path.txt`          | Rutas de páginas spam generadas                             |
| `public/sitemap-*.xml`     | Sitemaps falsos para indexación en Google                   |
| `public/sitemap-index.xml` | Índice de sitemaps spam                                     |
| `public/.vanta_notified`   | Archivo de persistencia del atacante                        |
| `public/media/`            | Carpeta inyectada, no era del proyecto                      |
| `public/static/`           | Carpeta inyectada, no era del proyecto                      |
| `public/index.php`         | Infectado con inyector SEO (2.34 KB vs ~300 bytes original) |
| `public/robots.txt`        | Modificado para permitir indexación total                   |

---

## Correcciones implementadas en el código (aplicadas vía git)

Aplicadas en `cobranzasv4` y `consortiumv4`, rama `main`:

| Corrección                                    | Archivos afectados                                                                       |
| --------------------------------------------- | ---------------------------------------------------------------------------------------- |
| Regla `NoExecutableFile` en todos los uploads | `CasoDocumentManager`, `MovimientoDocumentManager`, `DocumentsManager`                   |
| Autenticación requerida en endpoints de API   | `routes/api.php` — `/api/user-roles` y `/api/user-assignments`                           |
| `basename()` en descargas de reportes         | `ReportCaso`, `ReportInvoice`, `ReportMovimiento`, `ReportProforma`, `ReportTransaction` |
