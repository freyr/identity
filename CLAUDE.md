# Freyr Identity

PHP library providing `Id` value object wrapping monotonic ULID via symfony/uid. Thin adapter for domain identifiers.

## Critical Rules

- **ID001**: Constructor is private — use factory methods: `Id::new()`, `Id::fromBinary()`, `Id::fromString()`.
- **ID002**: `Id` is immutable with final methods — no inheritance.
- **ID003**: Equality via `sameAs()`, not `===`.
- **ID004**: PHPStan max level with bleeding edge + all strict rules.

## Commands

```bash
docker compose run --rm php vendor/bin/phpunit --testdox          # Tests
docker compose run --rm php vendor/bin/phpstan --memory-limit=-1  # Static analysis
docker compose run --rm php vendor/bin/ecs check --fix            # Code style
docker compose run --rm php vendor/bin/rector process --dry-run   # PHP 8.2 upgrade rules
docker compose run --rm php vendor/bin/infection --threads=max     # Mutation testing (minMsi 85)
docker compose run --rm php sh                                    # Shell
make qa                                                           # All CI checks (incl. coverage floor, composer normalize/audit)
```

Line coverage floor is 100% (`MIN_COVERAGE` in `.github/workflows/ci.yml`); a new branch without a test fails CI.

## Architecture

Single-class library. Namespace: `Freyr\Identity\`. Source: `src/Id.php`.

Cache directory: `cache/` (phpstan, ecs, phpunit, infection, rector).

## Releases

- PR titles follow Conventional Commits; the squash commit title sets the version (`feat` minor, `fix`/`perf` patch, `!` major, other types none).
- The release workflow on `main` pushes `vX.Y.Z` and creates the GitHub release. Never create a tag by hand and never add entries to `CHANGELOG.md`.
- Release config: `.github/release.json`. Required checks: `pr-title`, `version`, `ci-gate`; every new CI job goes into `ci-gate.needs`.

## Boundaries

**ASK FIRST:**
- Changes to the public API of `Id` class
- New dependencies
