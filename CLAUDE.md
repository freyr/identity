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
docker compose run --rm php sh                                    # Shell
```

## Architecture

Single-class library. Namespace: `Freyr\Identity\`. Source: `src/Id.php`.

Cache directory: `cache/` (phpstan, ecs, phpunit).

## Boundaries

**ASK FIRST:**
- Changes to the public API of `Id` class
- New dependencies
