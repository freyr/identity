<?php

declare(strict_types=1);


use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Id
{
    final private function __construct(readonly protected UuidInterface $id)
    {
    }

    final public static function new(): static
    {
        return new static(Uuid::uuid7());
    }

    final public static function fromBinary(string $bytes): static
    {
        $uuid = Uuid::fromBytes($bytes);
        return new static($uuid);
    }

    final public static function fromString(string $uuid): static
    {
        return new static(Uuid::fromString($uuid));
    }

    public function __toString(): string
    {
        return $this->id->toString();
    }

    public function toBinary(): string
    {
        return $this->id->toString();
    }

    public function sameAs(Id $id): bool
    {
        return $this->id->equals($id->id);
    }
}
