<?php

declare(strict_types=1);

namespace Freyr\Identity;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Id>
 */
class IdCollection implements Countable, IteratorAggregate
{
    /**
     * @param array<Id> $ids
     */
    private function __construct(private readonly array $ids)
    {
    }

    /**
     * @param array<Id> $ids
     */
    public static function fromArray(array $ids): self
    {
        return new self(array_values($ids));
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function add(Id $id): self
    {
        $ids = $this->ids;
        $ids[] = $id;
        return new self($ids);
    }

    public function remove(Id $id): self
    {
        $ids = array_filter(
            $this->ids,
            static fn (Id $existingId): bool => !$existingId->sameAs($id)
        );

        return new self(array_values($ids));
    }

    public function contains(Id $id): bool
    {
        return array_any($this->ids, fn ($existingId) => $existingId->sameAs($id));
    }

    public function isEmpty(): bool
    {
        return $this->ids === [];
    }

    public function first(): ?Id
    {
        return $this->ids[0] ?? null;
    }

    public function last(): ?Id
    {
        $count = count($this->ids);
        return $count > 0 ? $this->ids[$count - 1] : null;
    }

    public function count(): int
    {
        return count($this->ids);
    }

    /**
     * @return array<Id>
     */
    public function toArray(): array
    {
        return $this->ids;
    }

    /**
     * @param callable(Id): bool $callback
     */
    public function filter(callable $callback): self
    {
        $ids = array_filter($this->ids, $callback);
        return new self(array_values($ids));
    }

    /**
     * @template T
     * @param callable(Id): T $callback
     * @return array<T>
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->ids);
    }

    /**
     * @return Traversable<int, Id>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->ids);
    }

    public function merge(IdCollection $other): self
    {
        $array = [...$this->ids, ...$other->ids];
        return new self(array_values($array));
    }

    public function intersect(IdCollection $other): self
    {
        $ids = array_uintersect(
            $this->ids,
            $other->ids,
            static fn (Id $a, Id $b): int => strcmp((string) $a, (string) $b)
        );

        return new self(array_values($ids));
    }

    /**
     * @return array<string>
     */
    public function toStringArray(): array
    {
        return array_map(
            static fn (Id $id): string => (string) $id,
            $this->ids
        );
    }

    /**
     * @return array<string>
     */
    public function toBinaryArray(): array
    {
        return array_map(
            static fn (Id $id): string => $id->toBinary(),
            $this->ids
        );
    }
}
