# Portfolio Hardening Plan

## Objetivo
Demo full-stack deployable (Railway) con:
- Catalogo con trailers (YouTube embed)
- Pipeline HLS asincrono para clips demo (max 20s / 25MB)
- Spatie roles/permisos
- Queue database
- 8-12 tests + CI
- Deploy estable (web + worker + postgres + volume)

---

# P0 - Bloqueadores (NO avanzar sin esto)

## Arquitectura
- [x] Rutas limpias (sin logica en web.php)
- [x] Limpieza legacy SPA/dead pages (sin `/dashboard#/`, sin endpoints web/api heredados, sin vistas rotas)
- [x] Dividir God Controller
- [x] Eliminar role_id === 1 y User::isAdmin()

## Seguridad
- [x] Instalar Spatie Permission
- [x] Roles: admin / user / premium
- [x] Policies + middleware aplicados

## Pipeline Base
- [x] Migracion + modelo video_assets
- [x] Queue database configurada
- [x] Worker funcional
- [x] ProbeVideoJob
- [x] HLS 720p (1 rendition)
- [x] Estados: pending|processing|ready|failed
- [x] Manejo robusto de errores FFmpeg

## Deploy
- [x] Docker prod limpio
- [x] Railway: web + worker + postgres + volume
- [x] storage:link
- [x] E2E probado (upload -> ready -> playback)

---

# P1 - Calidad Portfolio

- [x] 8-12 tests (feature first)
- [x] GitHub Actions CI
- [x] UI badges + feedback processing
- [x] Thumbnails job
- [x] Cleanup source job

## P1 - UI Foundation Sprint (Blade + Tailwind + Alpine)

- [x] UIF-01 Design tokens Cineclub en `tailwind.config.js` + base CSS en `resources/css/app.css`
- [x] UIF-02 Componentes atomicos `resources/views/components/ui/{button,input,badge,card-film,modal}.blade.php`
- [x] UIF-03 Layout shells `editorial-shell`, `admin-shell`, `auth-shell` y `top-nav`
- [x] UIF-04 Componente `x-ui.rail` con `overflow-x` + `scroll-snap` (sin autoplay)
- [x] UIF-05 Motion system consistente (150-250ms, ease-out/ease-in-out) aplicado a componentes base

## P1 - UI Sprint Mockup Integration

- [x] UIS-01 Home base: logo exacto del mockup + hero editorial + footer minimal
- [x] UIS-01a Ajuste UX: quitar icono `movie_filter` del logo y unificar Top Nav global en Home
- [x] UIS-02 Buscador `/search?q=` con paginacion y empty state
- [x] UIS-03 Hero alimentado por `featured` real en DB (fallback controlado)
- [x] UIS-04 Rails curatoriales con `x-ui.rail` en Home (sin look retail)
- [x] UIS-05 Detail real alineado a mockup `editorial_film_detail`
- [x] UIS-06 Player detail: trailer YouTube + fallback HLS demo clip
- [x] UIS-07 Datos demo consistentes via `php artisan app:demo-seed` (local first, API opcional)

## P1 - TMDB Import-Only Hardening

- [x] TMDB metadata columns en `contents` con unique `(tmdb_type, tmdb_id)`
- [x] Accessors en `Content` (`poster_url`, `backdrop_url`, `display_overview`, `display_runtime`) con fallback legacy
- [x] Service layer `TmdbClient` + `TmdbImportService` con cache TTL y errores controlados
- [x] Admin import UI (`GET /admin/tmdb/search`, `POST /admin/tmdb/import`) bajo `role:admin`
- [x] Sync command `tmdb:sync --limit=50` para registros stale (>30 dias)
- [x] Tests con `Http::fake()` para create/update/no-duplicate/search/token-missing
- [x] README actualizado con estrategia import-only + `TMDB_TOKEN`
- [x] Cleanup legado TMDB (`App\\Services\\TmdbImportService`, `TmdbContentSeeder`, env/config old keys)

---

# P2 - Diferenciadores

- [x] HLS 3 renditions
- [x] TMDB import (opcional)
- [x] TMDB seeder base (opcional, manual con API key)

---

# P1 - Admin Hardening (Spatie Source of Truth)

- [x] ADM-01 Normalizar roles Spatie + comando `app:make-admin {email}` (sin checks por `role_id`)
- [x] ADM-02 Desbloqueo UI admin consistente (nav + botones contextuales solo admin)
- [x] ADM-03 Flujo CRUD admin unificado (movies/series create/edit/delete)
- [x] ADM-04 Flujo temporadas/episodios robusto (create/edit/delete sin rutas huerfanas)
- [x] ADM-05 Tests de permisos y visibilidad admin en vistas clave

---

# P1 - Admin Navigation Sprint

- [x] AN-01 Agrupar navegacion admin en dropdown unico en top-nav (Dashboard, Movies, Series, Add Content, TMDB Import, Video Assets, Health)
- [x] AN-02 Refinar iconografia y estados hover de acciones admin
- [x] AN-03 Breadcrumbs discretos para admin y detalles de contenido

---

# P1 - Data & Media Sprint (Cineclub Consistency)

- [x] DM-01 Unificar render de posters/backdrops + carrusel funcional (drag + flechas + fallback)
- [x] DM-02 Migraciones `curated_lists` y `curated_list_items` (rank + unique list/content)
- [x] DM-03 Comando `curated:import` idempotente (CSV/JSON local, unresolved/ambiguous report)
- [x] DM-05 README con formato CSV/JSON para curated imports
- [x] DM-06 Sistema de iconos unico y consistente + aria-label en icon-only buttons

---

# P1 - Detail & Admin Polish

- [x] DP-01 Admin Health API (`/admin/health/api`) + dashboard `/admin/health` integrando video pipeline health
- [x] DP-02 watchEpisode overhaul editorial (contexto serie/temporada/episodio + prev/next + placeholders)
- [x] DP-03 Fix jitter poster movie detail (sticky/transform)
- [x] DP-04 Series detail overhaul alineado a layout editorial

---

# P1 - UI Polish & Consistency Sprint

- [x] UP-01 Search bar spacing (top nav)
- [x] UP-02 Watch episode spacing + buttons (description no debe romper layout)
- [x] UP-03 Footer minimalista consistente, visible solo en páginas públicas (no auth/admin)
- [x] UP-04 Eliminar “Join the club” y cerrar huecos
- [x] UP-05 Hero cycling (preferencia: server-side random per request; opcional 30s con transición)
- [x] UP-06 Corregir counts en `/movies` y `/series` (mostrar solo total relevante)
- [x] UP-07 Unificar tamaños de spans/botones en admin/edit (usar componentes, mismo height/padding/font)
- [x] UP-08 Eliminar labels “Editorial / Film Detail” y “Editorial / Series Detail”

---

# P1 - Layout Consistency Sprint

- [x] LC-01 Arreglar vista `/search` (padding/sangria, container, spacing coherente con catalogo)
- [x] LC-02 Arreglar vista `watchEpisode` (padding/sangria, spacing e interlineados)
- [x] LC-03 Refactor de copy en vistas: "movie" -> "film" (solo UI visible; rutas se mantienen)
- [x] LC-04 Arreglar cards film/series: bloque de datos sticky abajo, clamp de titulo, no baile por longitud
- [x] LC-05 Reordenar synopsis/description: mas pequena, sin cursivas, colocada entre botones y metadata

---

# P3 - UI Overhaul (Rediseño Total)

## Fundacion visual
- [x] Definir design tokens globales (color, spacing, radius, shadows, typography)
- [x] Unificar `app.blade.php`, `guest.blade.php` y `navigation.blade.php` bajo un solo sistema visual
- [x] Crear componentes base reutilizables (`card`, `badge`, `button`, `input`, `alert`, `empty-state`)

## Catalogo publico
- [x] Rehacer `content-list.blade.php` con grid/cards limpias y skeleton loading simple
- [x] Rehacer `viewMovie.blade.php` enfocando trailer/embed + metadata legible
- [x] Rehacer `viewSerie.blade.php` con lista de temporadas/episodios clara y CTA de reproduccion

## Admin UX
- [x] Redisenar `addContent.blade.php` (step-like form, validaciones visibles, mensajes consistentes)
- [x] Rehacer tablas admin (movies/series/seasons) con estados vacios, filtros basicos y acciones claras
- [x] Mejorar pantalla `video-assets/show.blade.php` (timeline de estados + thumbnail + links tecnicos)

## Calidad de interfaz
- [x] Normalizar jerarquia tipografica y espaciados en todas las vistas Blade activas
- [x] Eliminar estilos inline y CSS legacy no usado; mover a utilidades Tailwind/componentes
- [x] Asegurar responsive real (mobile-first) para dashboard, catalogo y admin
- [x] Pasar accesibilidad minima (focus visible, contraste AA aproximado, labels/aria)

## Entrega visual para portfolio
- [ ] Capturas finales (home, detalle, admin, pipeline status)
- [ ] Actualizar README con seccion UI/UX decisions + screenshots

---

# Definition of Done Global

- App deployada en Railway y accesible publicamente
- Upload clip -> processing -> ready -> reproduce HLS
- Tests en verde + CI
- Sin hardcodes de roles
- README claro con instrucciones + demo creds

## TODOs de riesgo detectados
- [x] Resolver ruta/vista legacy de temporadas: se elimino `/admin/addSeasons/{id}` por no uso y se mantiene flujo estable en `/admin/series/{id}/seasons` (`seasons.manage`).
- [x] Sustituir `User::isAdmin()` legacy (basado en `role_id`) por middleware `role:admin` + Spatie roles (`hasRole('admin')`), sin fallback legacy.
---

# P1 - UI Admin & Detail Polish Sprint

- [x] AP-01 Overhaul visual de "Admin Controls" en detalles de film/serie (header, acciones right-aligned, bloque informativo)
- [x] AP-02 Arreglar boton "Watch episode" en detalle de serie (sin salto de linea, altura consistente)
- [ ] AP-03 Arreglar dropdown "Import from TMDB" (caret nativo/estilos)
- [ ] AP-04 Footer links utiles: paginas publicas About + Credits + GitHub real
