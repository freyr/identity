<?php

declare(strict_types=1);

namespace Freyr\Identity;

use InvalidArgumentException;
use Symfony\Component\Uid\MaxUlid;
use Symfony\Component\Uid\NilUlid;
use Symfony\Component\Uid\Ulid;

/**
 * @phpstan-consistent-constructor
 */
class Id
{
    protected function __construct(
        protected Ulid $id,
    ) {
        if ($id instanceof NilUlid || $id instanceof MaxUlid) {
            throw new InvalidArgumentException('NIL and MAX ULIDs are not valid identifiers.');
        }
    }

    final public static function new(): static
    {
        return new static(new Ulid());
    }

    final public static function fromBinary(string $bytes): static
    {
        $ulid = Ulid::fromBinary($bytes);

        return new static($ulid);
    }

    final public static function fromString(string $id): static
    {
        return new static(Ulid::fromString($id));
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function toBinary(): string
    {
        return $this->id->toBinary();
    }

    public function sameAs(self $id): bool
    {
        return $this->id->equals($id->id);
    }
}
