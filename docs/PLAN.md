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

---

# P2 - Diferenciadores

- [x] HLS 3 renditions
- [x] TMDB import (opcional)
- [x] TMDB seeder base (opcional, manual con API key)

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
- [ ] Asegurar responsive real (mobile-first) para dashboard, catalogo y admin
- [ ] Pasar accesibilidad minima (focus visible, contraste AA aproximado, labels/aria)

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
