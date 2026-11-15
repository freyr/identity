<?php

declare(strict_types=1);

namespace Freyr\Identity;

use InvalidArgumentException;
use Symfony\Component\Uid\MaxUlid;
use Symfony\Component\Uid\NilUlid;
use Symfony\Component\Uid\Ulid;

class Id implements \Stringable
{
    final private function __construct(
        private readonly Ulid $id,
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

    /**
     * Compares the value only: identifiers of different classes with the same ULID are the same.
     */
    public function sameAs(self $id): bool
    {
        return $this->id->equals($id->id);
    }

    /**
     * Compares the value and the class.
     */
    public function equalTo(self $id): bool
    {
        return $id::class === static::class && $this->sameAs($id);
    }
}
