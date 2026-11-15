# Freyr Identity

Monotonic ULID identity objects for PHP domain models. Provides an immutable `Id` value object and an `IdCollection` for working with sets of identifiers.

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
$id = Id::fromString('01ARYZ6S41TSV4RRFFQ69G5FAV');
$id = Id::fromBinary($bytes);

(string) $id;    // ULID string (26 chars, Crockford Base32)
$id->toBinary(); // 16-byte binary
$id->sameAs($other);  // same ULID; the class is not compared
$id->equalTo($other); // same ULID and same class
```

Create an `Id` with `new()`, `fromString()` or `fromBinary()`. The constructor is private.

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

A breaking change goes out as a minor release: use `feat:` and state the break in the PR description. The release notes of each version list such changes.

A change only to tests, docs or Markdown files makes no release. After the merge, the release job pushes the tag `vX.Y.Z` and creates the GitHub release; Packagist picks up the tag. Do not create tags by hand. The [GitHub releases](https://github.com/freyr/identity/releases) are the changelog.

Required checks: `pr-title`, `version` and `ci-gate`.

## Licence

MIT
