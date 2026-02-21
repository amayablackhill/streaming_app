# 🚀 Desplegament de Laravel amb PHP-FPM, MySQL i NGINX en una màquina remota

Aquesta guia explica com desplegar manualment una aplicació Laravel en un entorn real (sense Docker) en una màquina remota, utilitzant PHP-FPM, MySQL i NGINX. El projecte Laravel es clona a `/var/www/html`.

## 1️⃣ Preparació Inicial

### 🔐 Accedir a la màquina remota

```bash
ssh usuari@ip-del-servidor
```

### 📦 Instal·lació de paquets bàsics

```bash
apk add --no-cache nodejs npm nano curl bash git
```

## 2️⃣ Clonar el projecte Laravel

```bash
cd /var/www/html
git clone https://github.com/usuari/pablo-app.git
cd pablo-app
```

## 3️⃣ Instal·lació de PHP i PHP-FPM

### 🔧 Instal·lar PHP 8.4 i components

```bash
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.4)"
apk add --no-cache php84-fpm php84-mysqli php84-pdo php84-pdo_mysql php84-openssl php84-tokenizer
```

### 👤 Crear usuari per executar PHP

```bash
addgroup -S www-data
adduser -S -G www-data www-data
```

### 🔐 Permisos per a Laravel

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### ⚙️ Fitxer `.env`

Edita `.env` per configurar la base de dades:

```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=root
DB_PASSWORD=
```

## 4️⃣ Instal·lació de Composer i dependències Laravel

```bash
composer install
composer require laravel/breeze --dev
php artisan breeze:install
npm install && npm run build
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

## 5️⃣ Configuració de PHP-FPM

### 📂 Crear directori per al socket

```bash
sudo mkdir -p /run/php
chown -R www-data:www-data /run/php
chmod 755 /run/php
```

### 📝 Editar `/etc/php84/php-fpm.d/www.conf`

Assegura’t que conté:

```
listen = /run/php/php8.4-fpm.sock
user = www-data
group = www-data
listen.owner = www-data
listen.group = www-data
```

### ▶️ Iniciar PHP-FPM

```bash
php-fpm84
```

## 6️⃣ Instal·lació i configuració de MariaDB

```bash
apk add mariadb mariadb-client
mkdir -p /run/mysqld
chown -R mysql:mysql /run/mysqld /var/lib/mysql
mysql_install_db --user=mysql --datadir=/var/lib/mysql
mysqld --user=mysql &
```

### 🛠️ Crear base de dades

```bash
mariadb
> CREATE DATABASE laravel_db;
> USE laravel_db;
> SHOW TABLES;
```

## 7️⃣ Configuració de NGINX

```bash
apk add --no-cache nginx
mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled
```

### 📝 Exemple complet de fitxer `/etc/nginx/sites-available/laravel.conf`

```nginx
server {
    listen 80;
    server_name localhost;

    # Definim el directori del projecte Laravel
    root /var/www/html/netflix/public;

    index index.php index.html index.htm;

    # Habilitar la gestio  d'errors
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;

    # Gestio d'errors amb htmls personalitzats
    error_page 401 403 404 /40x.html;
    error_page 500 501 502 503 /50x.html;

    location = /40x.html {
        root /var/www/html/errors;
    }
    location = /50x.html {
        root /var/www/html/errors;
    }

    # Configurar el maneig de les sol·licituds PHP
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Prevenir l'accés a fitxers sensibles
    location ~ /\.ht {
        deny all;
    }
}
```

### 🔗 Enllaç simbòlic per activar el lloc

```bash
ln -s /etc/nginx/sites-available/laravel.conf /etc/nginx/sites-enabled/
```

### 📝 Editar `/etc/nginx/nginx.conf` per incloure els llocs disponibles

```bash
nano /etc/nginx/nginx.conf
```

Dins del bloc `http`, afegeix si no hi és:

```nginx
include /etc/nginx/sites-enabled/*.conf;
```

## ✅ Verificació Final

### 🌍 Accedir a Laravel

Visita `http://ip-del-servidor` des del navegador.

### 📋 Comprovar logs d'errors

```bash
cat /var/log/nginx/error.log
```

## 🔁 Ordres útils

```bash
# Aturar serveis
pkill php-fpm84
pkill mysqld
nginx -s stop

# Reiniciar serveis
php-fpm84
mysqld --user=mysql &
nginx
```

# 🚀 Desplegament de Laravel amb PHP-FPM, MySQL i NGINX en una màquina remota

Aquesta guia explica com desplegar manualment una aplicació Laravel en un entorn real (sense Docker) en una màquina remota, utilitzant PHP-FPM, MySQL i NGINX. El projecte Laravel es clona a `/var/www/html`.

## 1️⃣ Preparació Inicial

### 🔐 Accedir a la màquina remota

```bash
ssh usuari@ip-del-servidor
```

### 📦 Instal·lació de paquets bàsics

```bash
apk add --no-cache nodejs npm nano curl bash git
```

## 2️⃣ Clonar el projecte Laravel

```bash
cd /var/www/html
git clone https://github.com/usuari/pablo-app.git
cd pablo-app
```

## 3️⃣ Instal·lació de PHP i PHP-FPM

### 🔧 Instal·lar PHP 8.4 i components

```bash
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.4)"
apk add --no-cache php84-fpm php84-mysqli php84-pdo php84-pdo_mysql php84-openssl php84-tokenizer
```

### 👤 Crear usuari per executar PHP

```bash
addgroup -S www-data
adduser -S -G www-data www-data
```

### 🔐 Permisos per a Laravel

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### ⚙️ Fitxer `.env`

Edita `.env` per configurar la base de dades:

```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=root
DB_PASSWORD=
```

## 4️⃣ Instal·lació de Composer i dependències Laravel

```bash
composer install
composer require laravel/breeze --dev
php artisan breeze:install
npm install && npm run build
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

## 5️⃣ Configuració de PHP-FPM

### 📂 Crear directori per al socket

```bash
sudo mkdir -p /run/php
chown -R www-data:www-data /run/php
chmod 755 /run/php
```

### 📝 Editar `/etc/php84/php-fpm.d/www.conf`

Assegura’t que conté:

```
listen = /run/php/php8.4-fpm.sock
user = www-data
group = www-data
listen.owner = www-data
listen.group = www-data
```

### ▶️ Iniciar PHP-FPM

```bash
php-fpm84
```

## 6️⃣ Instal·lació i configuració de MariaDB

```bash
apk add mariadb mariadb-client
mkdir -p /run/mysqld
chown -R mysql:mysql /run/mysqld /var/lib/mysql
mysql_install_db --user=mysql --datadir=/var/lib/mysql
mysqld --user=mysql &
```

### 🛠️ Crear base de dades

```bash
mariadb
> CREATE DATABASE laravel_db;
> USE laravel_db;
> SHOW TABLES;
```

## 7️⃣ Configuració de NGINX

```bash
apk add --no-cache nginx
mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled
```

### 📝 Exemple complet de fitxer `/etc/nginx/sites-available/laravel.conf`

```nginx
server {
    listen 80;
    server_name localhost;

    # Definim el directori del projecte Laravel
    root /var/www/html/netflix/public;

    index index.php index.html index.htm;

    # Habilitar la gestio  d'errors
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;

    # Gestio d'errors amb htmls personalitzats
    error_page 401 403 404 /40x.html;
    error_page 500 501 502 503 /50x.html;

    location = /40x.html {
        root /var/www/html/errors;
    }
    location = /50x.html {
        root /var/www/html/errors;
    }

    # Configurar el maneig de les sol·licituds PHP
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Prevenir l'accés a fitxers sensibles
    location ~ /\.ht {
        deny all;
    }
}
```

### 🔗 Enllaç simbòlic per activar el lloc

```bash
ln -s /etc/nginx/sites-available/laravel.conf /etc/nginx/sites-enabled/
```

### 📝 Editar `/etc/nginx/nginx.conf` per incloure els llocs disponibles

```bash
nano /etc/nginx/nginx.conf
```

Dins del bloc `http`, afegeix si no hi és:

```nginx
include /etc/nginx/sites-enabled/*.conf;
```

## ✅ Verificació Final

### 🌍 Accedir a Laravel

Visita `http://ip-del-servidor` des del navegador.

### 📋 Comprovar logs d'errors

```bash
cat /var/log/nginx/error.log
```

## 🔁 Ordres útils

```bash
# Aturar serveis
pkill php-fpm84
pkill mysqld
nginx -s stop

# Reiniciar serveis
php-fpm84
mysqld --user=mysql &
nginx
```

