# 📚 Documentació del Projecte Laravel

Benvingut a la documentació del projecte Laravel. Aquesta guia recull, pas a pas, tot el procés necessari per configurar, desplegar i automatitzar un entorn de treball Laravel, tant per a desenvolupament local amb Docker com per a desplegament real en un servidor remot. També s’inclou la configuració d’un sistema CI/CD amb GitLab per facilitar els processos de prova i desplegament.

## 🔧 1. Entorn de Desenvolupament amb Docker

🗂 **Fitxer:** `entorn-docker.md`  
Aprendràs com crear un entorn Laravel completament funcional amb Docker, configurant manualment els contenidors de MariaDB, PHP-FPM i NGINX. Inclou la creació de la base de dades, la instal·lació de dependències, configuració de permisos i servidor web.

## 🌐 2. Desplegament Manual en Entorn Real

🗂 **Fitxer:** `entorn-real.md`  
Guia detallada per desplegar una aplicació Laravel en una màquina remota, sense Docker. Inclou la instal·lació de PHP, MariaDB, NGINX i totes les configuracions necessàries per posar l’aplicació en producció.

## 🔒 3. Afegir HTTPS amb NGINX

🗂 **Fitxer:** `https.md`  
Complement per a entorns en producció. Explica com generar certificats SSL autosignats i configurar NGINX perquè accepti connexions segures HTTPS, amb redirecció automàtica des d’HTTP.

## 🚀 4. Automatització amb GitLab CI/CD

🗂 **Fitxer:** `gdi-dc.md`  
Configuració pas a pas d’un sistema CI/CD amb GitLab. Inclou:

- Estructura de branques (`pages`, `staging`, `deploy`)
- Execució automàtica de proves
- Desplegament automatitzat en entorn de producció
- Generació automàtica de documentació amb MkDocs