# 🐳 Entorn Laravel amb Docker (Pas a Pas)

Aquest document descriu detalladament com construir un entorn Laravel completament funcional amb Docker i imatges Alpine des de zero. No es fa servir cap imatge preconfigurada per tal d’aprendre el procés manualment.

---

## 📁 Estructura del Projecte

```
├── docker-compose.yml
├── Dockerfile-laravel
└── docs/
    ├── mysql.md
    ├── laravel-php-fpm.md
    └── nginx.md
```

---

## 🚧 Pas 1: Crear els Contenidors amb Docker Compose

### 🧱 Fitxer `docker-compose.yml`
Defineix 3 serveis:

- `mysql`: amb MariaDB
- `laravel`: amb PHP-FPM
- `nginx`: servidor web

També es defineix un volum compartit anomenat `laravel-app`.

#### 📄 Exemple de fitxer `docker-compose.yml`

```yaml
services:
  mysql:
    image: alpine:latest
    container_name: mysql
    command: tail -f /dev/null
    networks:
      - net-m8  

  laravel:
    build:
      context: .
      dockerfile: Dockerfile-laravel
    container_name: laravel
    volumes:
      - laravel-app:/laravel
    networks:
      - net-m8

  nginx:
    image: alpine:latest
    container_name: nginx
    ports:
      - "80:80"
    command: tail -f /dev/null
    volumes:
      - laravel-app:/var/www/html
    networks:
      - net-m8 

volumes: 
  laravel-app:

networks: 
  net-m8:
```

### ▶️ Executar:

```bash
docker-compose up -d
```

---

## 📦 Pas 2: Configurar el Contenidor MariaDB

```bash
docker exec -it mysql sh
```

1. Actualitza els paquets:
    ```sh
    apk update
    ```

2. Instal·la MariaDB:
    ```sh
    apk add mariadb mariadb-client
    ```

3. Inicialitza i arrenca el servidor:
    ```sh
    mysql_install_db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
    mysqld --user=mysql &
    ```

4. Permet connexions remotes:
    - Edita `/etc/my.cnf.d/mariadb-server.cnf`
    - Comenta `skip-networking` i estableix `bind-address=0.0.0.0`

5. Reinicia MariaDB:
    ```sh
    mysqladmin -u root shutdown
    mysqld --user=mysql &
    ```

6. Crea la base de dades per Laravel:
    ```sh
    mysql -u root
    > CREATE DATABASE laravel;
    ```

---

## 🛠️ Pas 3: Configurar Laravel i PHP-FPM

```bash
docker exec -it laravel bash
```

1. Instal·la dependències:
    ```sh
    apk add php84 php84-fpm php84-mysqli php84-pdo php84-pdo_mysql php84-openssl php84-tokenizer curl composer nodejs npm bash
    ```

2. Configura l’usuari:
    ```sh
    addgroup -S www-data
    adduser -S www-data -G www-data
    ```

3. Accedeix al volum compartit i crea el projecte:
    ```sh
    cd /var/www
    laravel new nom-projecte
    cd nom-projecte
    ```

4. Modifica `.env` per connectar amb MariaDB:
    ```
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=laravel
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. Assigna permisos:
    ```sh
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    ```

6. Aplica les migracions:
    ```sh
    php artisan migrate
    ```

7. Instal·la Laravel Breeze:
    ```sh
    composer require laravel/breeze --dev
    php artisan breeze:install
    npm install && npm run dev
    ```

8. Inicia PHP-FPM:
    ```sh
    php-fpm84
    ```

---

## 🌐 Pas 4: Configurar NGINX

```bash
docker exec -it nginx sh
```

1. Instal·la NGINX:
    ```sh
    apk update
    apk add nginx nano
    ```

2. Crea estructura:
    ```sh
    mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled
    ```

3. Crea fitxer de configuració Laravel:
    ```nginx
    server {
        listen 80;
        server_name localhost;
        root /var/www/nom-projecte/public;

        index index.php index.html;

        access_log /var/log/nginx/access.log;
        error_log /var/log/nginx/error.log;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass laravel:9000;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_index index.php;
        }
    }
    ```

4. Enllaç simbòlic:
    ```sh
    ln -s /etc/nginx/sites-available/laravel.conf /etc/nginx/sites-enabled/
    ```

5. Edita `/etc/nginx/nginx.conf` per incloure `sites-enabled`:
    ```nginx
    include /etc/nginx/sites-enabled/*.conf;
    ```

6. Reinicia NGINX:
    ```sh
    nginx
    nginx -s reload
    ```

---

## 🧪 Verificació

- Laravel hauria de respondre correctament en accedir a `http://localhost`
- Revisa els logs:
    ```sh
    cat /var/log/nginx/error.log
    ```

---

## 🧼 Comandes útils

```sh
# Entrar als contenidors
docker exec -it laravel bash
docker exec -it nginx sh
docker exec -it mysql sh

# Aturar contenidors
docker-compose down
```
