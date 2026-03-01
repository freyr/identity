<?php

declare(strict_types=1);

namespace Freyr\Identity\Tests;

use Freyr\Identity\Id;
use Freyr\Identity\IdCollection;
use PHPUnit\Framework\TestCase;

final class IdCollectionTest extends TestCase
{
    public function testFromArrayCreatesCollection(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2]);

        self::assertCount(2, $collection);
        self::assertTrue($collection->contains($id1));
        self::assertTrue($collection->contains($id2));
    }

    public function testEmptyCreatesEmptyCollection(): void
    {
        $collection = IdCollection::empty();

        self::assertCount(0, $collection);
        self::assertTrue($collection->isEmpty());
    }

    public function testAddAddsIdToCollection(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection = IdCollection::empty()
            ->add($id1)
            ->add($id2);

        self::assertCount(2, $collection);
        self::assertTrue($collection->contains($id1));
        self::assertTrue($collection->contains($id2));
    }

    public function testAddIsImmutable(): void
    {
        $id = Id::new();
        $collection1 = IdCollection::empty();
        $collection2 = $collection1->add($id);

        self::assertCount(0, $collection1);
        self::assertCount(1, $collection2);
    }

    public function testRemoveRemovesIdFromCollection(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();
        $id3 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2, $id3])
            ->remove($id2);

        self::assertCount(2, $collection);
        self::assertTrue($collection->contains($id1));
        self::assertFalse($collection->contains($id2));
        self::assertTrue($collection->contains($id3));
    }

    public function testRemoveIsImmutable(): void
    {
        $id = Id::new();
        $collection1 = IdCollection::fromArray([$id]);
        $collection2 = $collection1->remove($id);

        self::assertCount(1, $collection1);
        self::assertCount(0, $collection2);
    }

    public function testContainsReturnsTrueWhenIdExists(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2]);

        self::assertTrue($collection->contains($id1));
        self::assertTrue($collection->contains($id2));
    }

    public function testContainsReturnsFalseWhenIdDoesNotExist(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();
        $id3 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2]);

        self::assertFalse($collection->contains($id3));
    }

    public function testIsEmptyReturnsTrueForEmptyCollection(): void
    {
        $collection = IdCollection::empty();

        self::assertTrue($collection->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyCollection(): void
    {
        $id = Id::new();
        $collection = IdCollection::fromArray([$id]);

        self::assertFalse($collection->isEmpty());
    }

    public function testFirstReturnsFirstId(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();
        $id3 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2, $id3]);

        self::assertTrue($collection->first()?->sameAs($id1) ?? false);
    }

    public function testFirstReturnsNullForEmptyCollection(): void
    {
        $collection = IdCollection::empty();

        self::assertNull($collection->first());
    }

    public function testLastReturnsLastId(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();
        $id3 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2, $id3]);

        self::assertTrue($collection->last()?->sameAs($id3) ?? false);
    }

    public function testLastReturnsNullForEmptyCollection(): void
    {
        $collection = IdCollection::empty();

        self::assertNull($collection->last());
    }

    public function testCountReturnsNumberOfIds(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2]);

        self::assertCount(2, $collection);
        self::assertSame(2, $collection->count());
    }

    public function testToArrayReturnsArrayOfIds(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2]);
        $array = $collection->toArray();

        self::assertIsArray($array);
        self::assertCount(2, $array);
        self::assertSame($id1, $array[0]);
        self::assertSame($id2, $array[1]);
    }

    public function testFilterFiltersIdsBasedOnCallback(): void
    {
        $id1 = Id::fromString('01ARYZ6S41TSV4RRFFQ69G5FA1');
        $id2 = Id::fromString('01ARYZ6S41TSV4RRFFQ69G5FA2');
        $id3 = Id::fromString('01ARYZ6S41TSV4RRFFQ69G5FA3');

        $collection = IdCollection::fromArray([$id1, $id2, $id3]);

        $filtered = $collection->filter(
            static fn (Id $id): bool => str_ends_with((string) $id, '1') || str_ends_with((string) $id, '3')
        );

        self::assertCount(2, $filtered);
        self::assertTrue($filtered->contains($id1));
        self::assertFalse($filtered->contains($id2));
        self::assertTrue($filtered->contains($id3));
    }

    public function testMapMapsIdsToOtherValues(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2]);
        $strings = $collection->map(static fn (Id $id): string => (string) $id);

        self::assertIsArray($strings);
        self::assertCount(2, $strings);
        self::assertSame((string) $id1, $strings[0]);
        self::assertSame((string) $id2, $strings[1]);
    }

    public function testCollectionIsIterable(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2]);

        $ids = [];
        foreach ($collection as $id) {
            $ids[] = $id;
        }

        self::assertCount(2, $ids);
        self::assertSame($id1, $ids[0]);
        self::assertSame($id2, $ids[1]);
    }

    public function testMergeCombinesTwoCollections(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();
        $id3 = Id::new();

        $collection1 = IdCollection::fromArray([$id1, $id2]);
        $collection2 = IdCollection::fromArray([$id3]);

        $merged = $collection1->merge($collection2);

        self::assertCount(3, $merged);
        self::assertTrue($merged->contains($id1));
        self::assertTrue($merged->contains($id2));
        self::assertTrue($merged->contains($id3));
    }

    public function testMergeIsImmutable(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection1 = IdCollection::fromArray([$id1]);
        $collection2 = IdCollection::fromArray([$id2]);
        $merged = $collection1->merge($collection2);

        self::assertCount(1, $collection1);
        self::assertCount(1, $collection2);
        self::assertCount(2, $merged);
    }

    public function testToStringArrayReturnsArrayOfStrings(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2]);
        $strings = $collection->toStringArray();

        self::assertIsArray($strings);
        self::assertCount(2, $strings);
        self::assertSame((string) $id1, $strings[0]);
        self::assertSame((string) $id2, $strings[1]);
    }

    public function testToBinaryArrayReturnsArrayOfBinaryStrings(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection = IdCollection::fromArray([$id1, $id2]);
        $binaries = $collection->toBinaryArray();

        self::assertIsArray($binaries);
        self::assertCount(2, $binaries);
        self::assertSame($id1->toBinary(), $binaries[0]);
        self::assertSame($id2->toBinary(), $binaries[1]);
    }

    public function testFromArrayReindexesArray(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection = IdCollection::fromArray([
            5 => $id1,
            10 => $id2,
        ]);
        $array = $collection->toArray();

        self::assertSame($id1, $array[0]);
        self::assertSame($id2, $array[1]);
    }

    public function testIntersectReturnsCommonIds(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();
        $id3 = Id::new();
        $id4 = Id::new();

        $collection1 = IdCollection::fromArray([$id1, $id2, $id3]);
        $collection2 = IdCollection::fromArray([$id2, $id3, $id4]);

        $intersection = $collection1->intersect($collection2);

        self::assertCount(2, $intersection);
        self::assertFalse($intersection->contains($id1));
        self::assertTrue($intersection->contains($id2));
        self::assertTrue($intersection->contains($id3));
        self::assertFalse($intersection->contains($id4));
    }

    public function testIntersectReturnsEmptyWhenNoCommonIds(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();
        $id3 = Id::new();
        $id4 = Id::new();

        $collection1 = IdCollection::fromArray([$id1, $id2]);
        $collection2 = IdCollection::fromArray([$id3, $id4]);

        $intersection = $collection1->intersect($collection2);

        self::assertCount(0, $intersection);
        self::assertTrue($intersection->isEmpty());
    }

    public function testIntersectWithEmptyCollectionReturnsEmpty(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        $collection1 = IdCollection::fromArray([$id1, $id2]);
        $collection2 = IdCollection::empty();

        $intersection = $collection1->intersect($collection2);

        self::assertCount(0, $intersection);
        self::assertTrue($intersection->isEmpty());
    }

    public function testIntersectIsImmutable(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();
        $id3 = Id::new();

        $collection1 = IdCollection::fromArray([$id1, $id2]);
        $collection2 = IdCollection::fromArray([$id2, $id3]);

        $intersection = $collection1->intersect($collection2);

        self::assertCount(2, $collection1);
        self::assertCount(2, $collection2);
        self::assertCount(1, $intersection);
    }
}
