# Freyr Identity

Monotonic identity objects for PHP domain models, readable as a ULID and as a UUID v7. Provides an immutable `Id` value object and an `IdCollection` for working with sets of identifiers.

## Installation

```sh
composer require freyr/identity
```

Requires PHP 8.2 or later. CI tests PHP 8.2, 8.3, 8.4 and 8.5.

## Usage

### Id

Create, convert and compare identifiers.

```php
use Freyr\Identity\Id;

$id = Id::new();
$id = Id::fromString('01ARYZ6S41TSV4RRFFQ69G5FAV');                   // ULID string
$id = Id::fromString('01563df3-6481-d676-4c61-efb99302bd5b');         // UUID string
$id = Id::fromBinary($bytes);

(string) $id;    // ULID string (26 chars, Crockford Base32)
$id->toUuid();   // UUID string (36 chars, RFC 4122)
$id->toBinary(); // 16-byte binary
$id->sameAs($other);  // same 128 bits; the class is not compared
$id->equalTo($other); // same 128 bits and same class
```

Create an `Id` with `new()`, `fromString()` or `fromBinary()`. The constructor is private.

#### ULID and UUID v7

An `Id` holds 128 bits. The ULID string, the UUID string and the binary form are three views of the same bits, and `sameAs()` compares the bits: an `Id` from a ULID string is the same as an `Id` from the UUID string of that ULID.

`Id::new()` creates the bits in the UUID v7 layout. The result is a valid ULID and a valid UUID v7 at the same time, with the same timestamp and the same monotonic order in the two forms. Store it as `BINARY(16)` with `toBinary()`, or in a `uuid` column with `toUuid()`.

`fromString()` detects the format and accepts a ULID string or a UUID string of any version. An identifier that was not created in the UUID v7 layout — an `Id` from version 1 of this library, or a plain ULID from another source — still has a `toUuid()` form that a database accepts, but that form is not a valid UUID v7.

Extend for typed identifiers.

```php
class UserId extends Id {}
class OrderId extends Id {}

$userId = UserId::new();
$orderId = OrderId::fromString((string) $userId);

$userId->sameAs($orderId);  // true
$userId->equalTo($orderId); // false
```

A subclass cannot define a constructor or read the internal ULID.

### IdCollection

Immutable collection with standard operations.

```php
use Freyr\Identity\IdCollection;

$collection = IdCollection::fromArray([$id1, $id2]);
$collection = IdCollection::empty();

$collection->add($id);
$collection->remove($id);
$collection->contains($id);
$collection->merge($other);
$collection->intersect($other);
$collection->filter(fn (Id $id) => /* ... */);
$collection->map(fn (Id $id) => /* ... */);

$collection->first();
$collection->last();
$collection->count();
$collection->isEmpty();
$collection->toArray();
$collection->toStringArray();
$collection->toBinaryArray();
```

All mutating methods return new instances.

## Upgrade from 1.x to 2.0

Version 2.0 has two breaking changes in `Id`:

- The constructor is `final private`. Replace `new UserId($ulid)` in a subclass or a factory with `UserId::fromString()` or `UserId::fromBinary()`, and remove each constructor that a subclass defines.
- The internal ULID is `private readonly`. A subclass cannot read `$this->id`; use `(string) $id`, `toBinary()` or `toUuid()`.

Version 2.0 also adds `equalTo()` and `toUuid()`, creates new identifiers in the UUID v7 layout, and allows `symfony/uid` 8. Stored identifiers stay valid: the ULID string and the 16 bytes of an identifier do not change.

## Doctrine

[`examples/DoctrineIdType.php`](examples/DoctrineIdType.php) is a Doctrine DBAL 4 type that stores an `Id` as 16 bytes. Copy it into your project; the library has no dependency on Doctrine.

## Development

The tools run in Docker.

```sh
make install   # install the dependencies
make test      # PHPUnit
make qa        # all CI checks
```

`make qa` runs the tests, the line coverage, mutation testing (Infection), PHPStan, ECS, Rector and the Composer checks. CI enforces:

- 100% line coverage;
- a mutation score (MSI) of 98% or more — a test must fail when the code changes, so assert exact values;
- PHPStan at max level on `src/` and `tests/`;
- tests on PHP 8.2, 8.3, 8.4 and 8.5, and on PHP 8.2 with the lowest dependency versions.

## Contributing

All changes go through a pull request with a [Conventional Commits](https://www.conventionalcommits.org/) title. The PR is squash-merged, so its title becomes the commit on `main` and sets the next version:

| PR title | Release |
|---|---|
| `feat: …` | minor |
| `fix: …`, `perf: …`, `revert: …` | patch |
| `feat!: …`, or `BREAKING CHANGE:` in the PR body | major — only with the explicit approval of the maintainer |
| `docs:`, `test:`, `ci:`, `build:`, `refactor:`, `chore:`, `style:` | none |

A breaking change always goes out as a major release, never as a minor or a patch release. A change is breaking when code that uses the public or protected API of version N can fail with version N+1: a removed or renamed method, a narrower visibility, a changed signature, or a different result for the same input.

A change only to tests, docs or Markdown files makes no release. After the merge, the release job pushes the tag `vX.Y.Z` and creates the GitHub release; Packagist picks up the tag. Do not create tags by hand. The [GitHub releases](https://github.com/freyr/identity/releases) are the changelog.

Required checks: `pr-title`, `version` and `ci-gate`.

## Licence

MIT
