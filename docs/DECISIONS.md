# Technical Decisions

## D-001 Monolith (Laravel + Blade) over SPA
- Decision: Keep monolith architecture.
- Why: Faster stabilization, lower infra complexity, easier recruiter walkthrough.
- Tradeoff: Less frontend decoupling than SPA.

## D-002 Database queue first
- Decision: Use `QUEUE_CONNECTION=database` by default.
- Why: Zero extra infra, easy local/prod parity.
- Tradeoff: Lower throughput than Redis.

## D-003 Async-only video processing
- Decision: FFmpeg runs only in Jobs.
- Why: Prevent request blocking/timeouts, improve reliability.
- Tradeoff: Requires queue observability.

## D-004 TMDB import-only model
- Decision: No TMDB calls in public runtime.
- Why: Deterministic UX, lower external dependency risk.
- Tradeoff: Metadata freshness depends on admin import/sync.

## D-005 Legal-safe public playback scope
- Decision: Public app focuses on trailers/demo clips.
- Why: Portfolio legality + lower storage/egress cost.
- Tradeoff: Not a full streaming catalog.

## D-006 Railway volume-based storage
- Decision: Persist `storage` on mounted volume.
- Why: HLS/thumbnails must survive restarts/deploys.
- Tradeoff: Volume management and mount checks required.

## D-007 Spatie Permission for admin gating
- Decision: Replace hardcoded role checks with role-based authorization.
- Why: Safer and maintainable authZ model.
- Tradeoff: Requires role seeding and migration discipline.

## D-008 Operational feature flags for incident response
- Decision: Add env-driven kill switches for admin writes and TMDB import surfaces.
- Why: During abuse/spikes, ops can disable risky write/import paths immediately without code changes.
- Tradeoff: Requires explicit runbook discipline and variable hygiene in production.
