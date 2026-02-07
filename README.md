# Freyr Identity

UUID v7 identity objects for PHP domain models. Provides an immutable `Id` value object and an `IdCollection` for working with sets of identifiers.

## Installation

```sh
composer require freyr/identity
```

Requires PHP 8.2+.

## Usage

### Id

Create, convert and compare identifiers.

```php
use Freyr\Identity\Id;

$id = Id::new();
$id = Id::fromString('01920a7c-8b00-7000-8000-000000000001');
$id = Id::fromBinary($bytes);

(string) $id;    // UUID string
$id->toBinary(); // 16-byte binary
$id->sameAs($other);
```

Extend for typed identifiers.

```php
class UserId extends Id {}

$userId = UserId::new();
```

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

## Licence

MIT
