# Portfolio Hardening Plan

## Objetivo
Demo full-stack deployable (Railway) con:
- Catálogo con trailers (YouTube embed)
- Pipeline HLS asíncrono para clips demo (max 20s / 25MB)
- Spatie roles/permisos
- Queue database
- 8–12 tests + CI
- Deploy estable (web + worker + postgres + volume)

---

# P0 — Bloqueadores (NO avanzar sin esto)

## Arquitectura
- [ ] Rutas limpias (sin lógica en web.php)
- [x] Dividir God Controller
- [ ] Eliminar role_id === 1 y User::isAdmin()

## Seguridad
- [ ] Instalar Spatie Permission
- [ ] Roles: admin / user / premium
- [ ] Policies + middleware aplicados

## Pipeline Base
- [ ] Migración + modelo video_assets
- [ ] Queue database configurada
- [ ] Worker funcional
- [ ] ProbeVideoJob
- [ ] HLS 720p (1 rendition)
- [ ] Estados: pending|processing|ready|failed
- [ ] Manejo robusto de errores FFmpeg

## Deploy
- [ ] Docker prod limpio
- [ ] Railway: web + worker + postgres + volume
- [ ] storage:link
- [ ] E2E probado (upload → ready → playback)

---

# P1 — Calidad Portfolio

- [ ] 8–12 tests (feature first)
- [ ] GitHub Actions CI
- [ ] UI badges + feedback processing
- [ ] Thumbnails job
- [ ] Cleanup source job

---

# P2 — Diferenciadores

- [ ] HLS 3 renditions
- [ ] TMDB import (opcional)

---

# Definition of Done Global

- App deployada en Railway y accesible públicamente
- Upload clip → processing → ready → reproduce HLS
- Tests en verde + CI
- Sin hardcodes de roles
- README claro con instrucciones + demo creds


## TODOs de riesgo detectados
- [x] Resolver ruta/vista legacy de temporadas: se eliminó `/admin/addSeasons/{id}` por no uso y se mantiene flujo estable en `/admin/series/{id}/seasons` (`seasons.manage`).
- [ ] Sustituir `User::isAdmin()` legacy (basado en `role_id`) por Spatie Permission en tarea de seguridad P0.
