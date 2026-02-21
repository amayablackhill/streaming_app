# 🔐 Afegir HTTPS i redirecció HTTP ➝ HTTPS en un entorn real amb NGINX

Aquest apartat parteix de la base que l'aplicació Laravel ja ha estat desplegada correctament seguint les guies anteriors. A continuació, s'explica com afegir connexió segura HTTPS i redirigir tot el tràfic HTTP cap a HTTPS utilitzant NGINX i certificats SSL propis.

---

## 1️⃣ Crear els certificats SSL a la màquina remota

Cal generar una clau privada i un certificat públic autosignat. Executa:

```bash
cd /etc/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout private.key -out public.crt
```

> Això generarà:
> - `/etc/ssl/private.key`
> - `/etc/ssl/public.crt`

---

## 2️⃣ Modificar la configuració de NGINX (`laravel.conf`)

Cal fer dues modificacions importants:

### 🔁 Afegir un nou bloc `server` que escolti al port 80

Aquest bloc redirigeix automàticament qualsevol petició HTTP cap a HTTPS:

```nginx
server {
    listen 80;
    return 301 https://$host$request_uri;
}
```

### 🔒 Modificar el bloc `server` existent per escoltar en HTTPS

Canvia el port a `443 ssl` i afegeix les rutes dels certificats generats:

```nginx
server {
    listen 443 ssl;
    server_name localhost;

    ssl_certificate /etc/ssl/public.crt;
    ssl_certificate_key /etc/ssl/private.key;

    # Definim el directori del projecte Laravel
    root /var/www/html/netflix/public;

    index index.php index.html index.htm;

    # Habilitar la gestió d'errors
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;

    # Gestió d'errors amb HTMLs personalitzats
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

---

## 3️⃣ Comprovar i reiniciar NGINX

### ✅ Validar la configuració

```bash
nginx -t
```

### 🔄 Reiniciar NGINX

```bash
nginx -s reload
```

---

Ara, qualsevol accés al teu lloc per HTTP serà automàticament redirigit cap a HTTPS, i la comunicació estarà protegida amb un certificat SSL.
