<?php

declare(strict_types=1);

namespace Freyr\Identity;

use InvalidArgumentException;
use Symfony\Component\Uid\MaxUlid;
use Symfony\Component\Uid\NilUlid;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\UuidV7;

/**
 * A 128-bit identifier, kept as one ULID.
 *
 * The constructor and the ULID are private since version 2.0: create an identifier with new(), fromString()
 * or fromBinary(). A typed identifier is an empty subclass.
 */
class Id implements \Stringable
{
    final private function __construct(
        private readonly Ulid $id,
    ) {
        if ($id instanceof NilUlid || $id instanceof MaxUlid) {
            throw new InvalidArgumentException('NIL and MAX ULIDs are not valid identifiers.');
        }
    }

    /**
     * Creates the 128 bits in the UUID v7 layout, so that the identifier is a valid ULID and a valid UUID v7.
     */
    final public static function new(): static
    {
        return new static(Ulid::fromBinary((new UuidV7())->toBinary()));
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
     * The RFC 4122 form of the same 128 bits. It is a valid UUID v7 for each identifier from new().
     */
    public function toUuid(): string
    {
        return $this->id->toRfc4122();
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
