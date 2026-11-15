# Freyr Identity

PHP library providing the `Id` value object, which wraps a monotonic ULID via symfony/uid, and `IdCollection`, an immutable list of `Id` objects.

## Critical Rules

- **ID001**: The `Id` constructor is `final private` — use the factory methods: `Id::new()`, `Id::fromBinary()`, `Id::fromString()`.
- **ID002**: `Id` is immutable (`private readonly` ULID). Typed identifiers are empty subclasses (`class UserId extends Id {}`); a subclass cannot define a constructor or read the ULID.
- **ID003**: Equality via `sameAs()` (the 128 bits only, ignores the class) or `equalTo()` (the 128 bits and the class), not `===`.
- **ID006**: One internal form: a ULID. `Id::new()` creates the bits in the UUID v7 layout, so each new `Id` is a valid ULID and a valid UUID v7. `__toString()` gives the ULID, `toUuid()` the UUID, `toBinary()` the 16 bytes. Never add a second internal type or a ULID/UUID "kind".
- **ID004**: PHPStan max level with bleeding edge + all strict rules, on `src/` and `tests/`.
- **ID005**: Tests assert exact literal values (no expected value computed by the code under test) and carry `#[CoversClass]`. Fixtures live in `tests/Fixtures/`.

## Commands

```bash
docker compose run --rm php vendor/bin/phpunit --testdox          # Tests
docker compose run --rm php vendor/bin/phpstan --memory-limit=-1  # Static analysis
docker compose run --rm php vendor/bin/ecs check --fix            # Code style
docker compose run --rm php vendor/bin/rector process --dry-run   # PHP 8.2 upgrade rules
docker compose run --rm php vendor/bin/infection --threads=max     # Mutation testing (minMsi 98)
docker compose run --rm php sh                                    # Shell
make qa                                                           # All CI checks (incl. coverage floor, composer normalize/audit)
```

Line coverage floor is 100% (`MIN_COVERAGE` in `.github/workflows/ci.yml`); a new branch without a test fails CI.

The Infection thresholds (`minMsi`, `minCoveredMsi` in `infection.json5`) only go up. Kill an escaped mutant with a test of observable behavior, or record why it is equivalent.

CI tests PHP 8.2 to 8.5, and PHP 8.2 with the lowest dependency versions. The dev image and the tool jobs use PHP 8.5.

## Architecture

Namespace: `Freyr\Identity\`. Source: `src/Id.php`, `src/IdCollection.php`. `examples/` holds a Doctrine DBAL type that no tool checks (`doctrine/dbal` is not a dependency).

Cache directory: `cache/` (phpstan, ecs, phpunit, infection, rector).

## Releases

- PR titles follow Conventional Commits; the squash commit title sets the version (`feat` minor, `fix`/`perf` patch, `!` major, other types none).
- **Every release is a patch or a minor release, also for a breaking change.** Never put `!` in a PR title or `BREAKING CHANGE:` in a PR body without the explicit approval of the owner; state a breaking change in the PR description instead.
- The release workflow on `main` pushes `vX.Y.Z` and creates the GitHub release. Never create a tag by hand and never add entries to `CHANGELOG.md`.
- Release config: `.github/release.json`. Required checks: `pr-title`, `version`, `ci-gate`; every new CI job goes into `ci-gate.needs`.

## Boundaries

**ASK FIRST:**
- Changes to the public API of `Id` class
- New dependencies
