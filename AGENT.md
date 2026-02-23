# AGENT.md — Portfolio Hardening (Laravel Streaming)

Este repositorio se está preparando para ser un proyecto de portfolio **full-stack** y **deployable**.
La meta es terminarlo rápido sin sobre-ingeniería, manteniendo estándares profesionales.

## Objetivo del producto (demo legal y barata)
1. Catálogo con metadata (opcional: importación desde TMDB).
2. Playback principal del catálogo: **trailers (YouTube embed)**.  
   - No se suben películas completas por copyright/coste.
3. Feature diferencial: pipeline real con **FFmpeg + HLS** para **clips demo** (admin upload).  
   - Máx: **20s** de duración y **25MB**.
   - Genera `master.m3u8` + segments + thumbnails.
4. Deploy objetivo: **Railway** (gratis/fácil) con **Volume** para persistencia.

---

## Decisiones técnicas (NO renegociar)
- Laravel monolito con **Blade + Alpine** (no SPA).
- Auth: Laravel Breeze.
- Permisos: **Spatie Laravel Permission** (roles: `admin`, `user`, `premium`).
- Colas: `QUEUE_CONNECTION=database`.
- Pipeline asíncrono (Jobs):
  1) `ProbeVideoJob` (ffprobe -> metadata)
  2) `TranscodeToHlsJob` (HLS 720p primero; luego 3 renditions opcional)
  3) `GenerateThumbnailsJob`
  4) `CleanupSourceJob`
- Estado de procesamiento en DB: `pending|processing|ready|failed`.
- UI mínima pro: badges estado + feedback.
- Tests: 8–12 (feature tests primero) + CI GitHub Actions.

---

## Principios de implementación (anti-caos)
### 1) Una tarea por iteración
- No mezclar refactors con features.
- Cada tarea debe ser verificable en 1–3h.

### 2) No bloquear HTTP
- **Prohibido** ejecutar FFmpeg en controllers.
- Controllers crean registros, validan y despachan Jobs.

### 3) Rutas limpias
- `routes/web.php` y `routes/api.php` no contienen queries ni lógica.
- Se usan controllers + requests + policies.

### 4) Manejo robusto de errores
- Si falla ffmpeg/ffprobe: `status=failed`, `error_message` en DB, UI lo muestra.
- Guardar stderr resumido y/o path a log.

### 5) Deploy-first
- Cada cambio debe seguir siendo deployable con Docker.
- Config clara de `web` y `worker`.
- Persistencia de HLS/thumbs mediante Railway Volume.

---

## Estructura recomendada (mínimo viable)
- `app/Models/VideoAsset.php`
- `app/Jobs/ProbeVideoJob.php`
- `app/Jobs/TranscodeToHlsJob.php`
- `app/Jobs/GenerateThumbnailsJob.php`
- `app/Jobs/CleanupSourceJob.php`
- `app/Services/VideoUploadService.php` (o `VideoAssetService.php`)
- `app/Http/Controllers/Admin/*` (CRUD)
- `app/Http/Controllers/UploadController.php`
- `app/Http/Controllers/PlaybackController.php`
- `app/Http/Requests/StoreVideoRequest.php`

---

## Plan de trabajo (fuente de verdad)
- `docs/PLAN.md` contiene checklist P0/P1/P2 con tareas, estado y links a PR/commits.
- Cada tarea completada debe marcarse como DONE.

---

## Definition of Done (global)
- App compila y corre en local + Docker.
- 8–12 tests pasan en CI.
- Roles/permisos sin hardcode (`role_id===1` eliminado).
- Upload de clip -> cola -> HLS ready -> reproducción.
- Trailers funcionan en catálogo (YouTube embed).
- Deploy Railway operativo (web + worker + postgres + volume).
- README explica:
  - cómo correr local
  - cómo desplegar Railway
  - credenciales demo
  - decisiones técnicas

---

## Comandos de verificación (mínimos)
- `php artisan migrate:fresh --seed`
- `php artisan test`
- `php artisan queue:work --queue=video,default --tries=3 --timeout=3600`
- (si aplica) `npm ci && npm run build`

---

## Notas de licencia/copyright
- El catálogo usa trailers embebidos (YouTube) y/o clips demo cortos (propios o con licencia libre).
- No se distribuyen películas completas.

---

# Workflow de Desarrollo y Commits

## Branch Strategy

- Rama principal: `main`
- Rama de trabajo: `portfolio-hardening`
- Cada bloque grande debe de tener sub-branches:
  - `feature/video-pipeline`
  - `feature/spatie-permissions`
  - `feature/tests-ci`
  - `fix/nombre-del-fix`

Nunca trabajar directamente en `main`.

---

## Regla: 1 Tarea = 1 Commit

Cada tarea del PLAN.md debe:

1. Modificar solo lo necesario.
2. Compilar sin errores.
3. Mantener la app deployable.
4. Incluir migraciones limpias si aplica.
5. Actualizar PLAN.md marcando la tarea como completada.

---

## Convención de Commits


Reglas:
- Mensajes en inglés.
- Verbos en presente.
- Claros y específicos.
- Sin “fix stuff” o “misc changes”.

usar el formato: 

<type>(optional-scope): short description

ejemplos:

feat(video): add HLS 720p transcoding job
fix(auth): remove hardcoded role_id check
refactor(routes): move closures to controllers
test(video): add feature test for processing status
ci(github): add workflow for tests and lint
chore(deps): install spatie/laravel-permission


---

## Antes de cada commit

Ejecutar: 

- `php artisan test`




---

# UI / DESIGN SYSTEM — Obligatorio

Este proyecto NO puede parecer:
- SaaS genérico
- Template Tailwind
- Plataforma tech corporativa
- Netflix clone
- Letterboxd clone
- AI generated UI

Debe sentirse:

> Cineclub digital curado.
> Editorial.
> Cultural.
> Minimalista.
> Atemporal.
> Oscuro elegante.
> Exquisito al usar.

La experiencia es tan importante como la arquitectura backend.

---

# Dark Mode (Default)

La aplicación es dark-first.

No usar negro absoluto (#000).
No usar blanco puro (#FFF).
Evitar contrastes agresivos.

La atmósfera debe sentirse:
- Cálida
- Profunda
- Contemplativa
- Refinada

---

# DESIGN TOKENS (Fuente de Verdad Visual)

## Color System

### Background
- `bg-primary`: #141414 (carbón suave)
- `bg-surface`: #1C1C1C
- `bg-elevated`: #222222

### Text
- `text-primary`: #E8E6E3 (off-white cálido)
- `text-secondary`: #B8B5B0
- `text-muted`: #8A8782

### Accent (elegir uno dominante)
- Oliva oscuro: #3E4A3F
- Burdeos apagado: #5C2E2E
- Azul petróleo: #1F3A44
- Terracota profunda: #6B3F2B

Nunca usar rojo brillante estilo Netflix.

---

## Borders & Radius

- Border: 1px solid rgba(255,255,255,0.06)
- Radius base: 2px–4px máximo
- Nada de rounded-xl o extremos suaves tipo app móvil.

---

## Shadows

Sombras mínimas.
Preferir separación por:
- Espacio
- Bordes
- Contraste

Prohibido:
- shadow-2xl
- Glow
- Neón

---

# Tipografía

La tipografía es protagonista.

## Títulos
- Serif editorial
- Tamaño generoso
- Aire vertical amplio
- Contraste claro

## UI
- Sans neutra
- No más de 2 familias tipográficas totales

Evitar:
- Jerarquías comprimidas
- Texto pequeño estilo dashboard

---

# Layout Philosophy

✔ Scroll vertical natural  
✔ Grid respirado (2–4 columnas máximo)  
✔ Ritmo editorial  
✔ Hero artístico permitido (debe sentirse cinematográfico, no comercial)  
✔ Carousel permitido como mejora de UX, pero elegante y sobrio  

El carousel:
- No debe parecer retail.
- Sin autoplay agresivo.
- Sin animaciones dramáticas.
- Transiciones suaves y naturales.

---

# Motion & Interaction (Muy Importante)

La aplicación debe sentirse exquisita al usar.

Reglas:

- Animaciones suaves (150–250ms)
- ease-out o ease-in-out
- Nada elástico
- Nada agresivo
- Nada flashy

Hover:
- Subrayado animado
- Cambio sutil de contraste
- Ligera elevación
- No escalado exagerado

Transiciones:
- Deben sentirse cinematográficas.
- Deben mejorar la experiencia.
- Nunca distraer.

La UX debe transmitir:
- Control
- Fluidez
- Calma
- Calidad

---

# Component Rules

## Cards
- Minimalistas
- Imagen + título + año
- Sin overlays pesados
- Hover sutil

## Botones
- Simples
- Borde o fondo sólido elegante
- Sin gradientes

## Badges
- Pequeños
- Discretos
- Processing (tono cálido)
- Ready (tono suave)
- Failed (tono sobrio, no rojo brillante)

---

# Anti-Vibecode Rules

Si parece:
> “Podría estar en un boilerplate”

Rehacer.

Si parece:
> “Alguien tomó decisiones editoriales conscientes”

Correcto.

Evitar:
- Uso indiscriminado de clases Tailwind default
- Spacing repetitivo en todos los bloques
- Componentes sin identidad propia

---

# UI Review Checklist (Obligatorio antes de merge)

Antes de aprobar cualquier vista:

- ¿Se siente editorial?
- ¿Respira?
- ¿Evita look SaaS?
- ¿El dark mode es elegante?
- ¿Las animaciones son suaves y refinadas?
- ¿El hero se siente artístico y no comercial?
- ¿El carousel se siente curado y no retail?

Si alguna respuesta es NO → ajustar.
