<?php

declare(strict_types=1);

namespace Freyr\Identity\Tests;

use Freyr\Identity\Id;
use Freyr\Identity\IdCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IdCollectionTest extends TestCase
{
    private const A = '01ARYZ6S41TSV4RRFFQ69G5FA1';

    private const B = '01ARYZ6S41TSV4RRFFQ69G5FA2';

    private const C = '01ARYZ6S41TSV4RRFFQ69G5FA3';

    private const D = '01ARYZ6S41TSV4RRFFQ69G5FA4';

    public function testFromArrayKeepsTheOrderOfTheIds(): void
    {
        $collection = self::collection(self::C, self::A, self::B);

        self::assertSame([self::C, self::A, self::B], $collection->toStringArray());
    }

    public function testFromArrayReindexesTheKeys(): void
    {
        $collection = IdCollection::fromArray([
            5 => Id::fromString(self::A),
            10 => Id::fromString(self::B),
        ]);

        self::assertSame([self::A, self::B], $collection->toStringArray());
    }

    public function testFromArrayKeepsDuplicates(): void
    {
        $collection = self::collection(self::A, self::A);

        self::assertSame([self::A, self::A], $collection->toStringArray());
    }

    public function testEmptyCreatesACollectionWithNoIds(): void
    {
        $collection = IdCollection::empty();

        self::assertSame([], $collection->toArray());
        self::assertCount(0, $collection);
    }

    public function testAddAppendsTheIdAtTheEnd(): void
    {
        $collection = self::collection(self::B, self::A)->add(Id::fromString(self::C));

        self::assertSame([self::B, self::A, self::C], $collection->toStringArray());
    }

    public function testAddDoesNotChangeTheOriginalCollection(): void
    {
        $original = self::collection(self::A);

        $original->add(Id::fromString(self::B));

        self::assertSame([self::A], $original->toStringArray());
    }

    /**
     * @param list<string> $ids
     * @param list<string> $expected
     */
    #[DataProvider('provideRemoveCases')]
    public function testRemoveReturnsTheIdsThatAreNotTheSameAsTheGivenId(
        array $ids,
        string $removed,
        array $expected,
    ): void {
        $collection = self::collection(...$ids)->remove(Id::fromString($removed));

        self::assertSame($expected, $collection->toStringArray());
    }

    /**
     * @return iterable<string, array{list<string>, string, list<string>}>
     */
    public static function provideRemoveCases(): iterable
    {
        yield 'the first id' => [[self::A, self::B, self::C], self::A, [self::B, self::C]];
        yield 'an id in the middle' => [[self::A, self::B, self::C], self::B, [self::A, self::C]];
        yield 'the last id' => [[self::A, self::B, self::C], self::C, [self::A, self::B]];
        yield 'an id that is not in the collection' => [[self::A, self::B], self::D, [self::A, self::B]];
        yield 'each copy of a duplicate' => [[self::A, self::B, self::A], self::A, [self::B]];
        yield 'the only id' => [[self::A], self::A, []];
        yield 'an id from an empty collection' => [[], self::A, []];
    }

    public function testRemoveOfTheFirstIdMakesTheSecondIdTheFirst(): void
    {
        $collection = self::collection(self::A, self::B)->remove(Id::fromString(self::A));

        self::assertSame(self::B, (string) $collection->first());
    }

    public function testRemoveDoesNotChangeTheOriginalCollection(): void
    {
        $original = self::collection(self::A, self::B);

        $original->remove(Id::fromString(self::A));

        self::assertSame([self::A, self::B], $original->toStringArray());
    }

    public function testContainsReturnsTrueForAnIdWithTheSameValue(): void
    {
        $collection = self::collection(self::A, self::B);

        self::assertTrue($collection->contains(Id::fromString(self::B)));
    }

    public function testContainsReturnsFalseForAnIdThatIsNotInTheCollection(): void
    {
        $collection = self::collection(self::A, self::B);

        self::assertFalse($collection->contains(Id::fromString(self::C)));
    }

    public function testContainsReturnsFalseForAnEmptyCollection(): void
    {
        self::assertFalse(IdCollection::empty()->contains(Id::fromString(self::A)));
    }

    public function testIsEmptyReturnsTrueForACollectionWithNoIds(): void
    {
        self::assertTrue(IdCollection::empty()->isEmpty());
    }

    public function testIsEmptyReturnsFalseForACollectionWithOneId(): void
    {
        self::assertFalse(self::collection(self::A)->isEmpty());
    }

    public function testFirstReturnsTheFirstId(): void
    {
        $collection = self::collection(self::B, self::A, self::C);

        self::assertSame(self::B, (string) $collection->first());
    }

    public function testLastReturnsTheLastId(): void
    {
        $collection = self::collection(self::B, self::A, self::C);

        self::assertSame(self::C, (string) $collection->last());
    }

    public function testFirstAndLastReturnTheOnlyIdOfACollectionWithOneId(): void
    {
        $id = Id::fromString(self::A);
        $collection = IdCollection::fromArray([$id]);

        self::assertSame($id, $collection->first());
        self::assertSame($id, $collection->last());
    }

    public function testFirstReturnsNullForAnEmptyCollection(): void
    {
        self::assertNull(IdCollection::empty()->first());
    }

    public function testLastReturnsNullForAnEmptyCollection(): void
    {
        self::assertNull(IdCollection::empty()->last());
    }

    public function testCountReturnsTheNumberOfIdsWithDuplicates(): void
    {
        self::assertCount(3, self::collection(self::A, self::B, self::A));
    }

    public function testToArrayReturnsTheSameIdObjectsInOrder(): void
    {
        $first = Id::fromString(self::B);
        $second = Id::fromString(self::A);

        $collection = IdCollection::fromArray([$first, $second]);

        self::assertSame([$first, $second], $collection->toArray());
    }

    public function testFilterKeepsTheIdsThatTheCallbackAcceptsInOrder(): void
    {
        $collection = self::collection(self::A, self::B, self::C, self::D);

        $filtered = $collection->filter(
            static fn (Id $id): bool => in_array((string) $id, [self::D, self::B], true)
        );

        self::assertSame([self::B, self::D], $filtered->toStringArray());
    }

    public function testFilterReturnsAnEmptyCollectionWhenTheCallbackAcceptsNoId(): void
    {
        $filtered = self::collection(self::A, self::B)->filter(static fn (Id $id): bool => false);

        self::assertSame([], $filtered->toArray());
    }

    public function testFilterDoesNotChangeTheOriginalCollection(): void
    {
        $original = self::collection(self::A, self::B);

        $original->filter(static fn (Id $id): bool => false);

        self::assertSame([self::A, self::B], $original->toStringArray());
    }

    public function testMapReturnsTheCallbackResultOfEachIdInOrder(): void
    {
        $collection = self::collection(self::B, self::A);

        $result = $collection->map(static fn (Id $id): string => 'id:' . substr((string) $id, -1));

        self::assertSame(['id:2', 'id:1'], $result);
    }

    public function testMapReturnsAnEmptyArrayForAnEmptyCollection(): void
    {
        $result = IdCollection::empty()->map(static fn (Id $id): string => (string) $id);

        self::assertSame([], $result);
    }

    public function testIterationGivesTheIdObjectsInOrderWithListKeys(): void
    {
        $first = Id::fromString(self::B);
        $second = Id::fromString(self::A);

        $collection = IdCollection::fromArray([$first, $second]);

        self::assertSame([$first, $second], iterator_to_array($collection));
    }

    /**
     * @param list<string> $left
     * @param list<string> $right
     * @param list<string> $expected
     */
    #[DataProvider('provideMergeCases')]
    public function testMergeAppendsTheIdsOfTheOtherCollection(array $left, array $right, array $expected): void
    {
        $merged = self::collection(...$left)->merge(self::collection(...$right));

        self::assertSame($expected, $merged->toStringArray());
    }

    /**
     * @return iterable<string, array{list<string>, list<string>, list<string>}>
     */
    public static function provideMergeCases(): iterable
    {
        yield 'two ids and two ids' => [[self::A, self::B], [self::C, self::D], [self::A, self::B, self::C, self::D]];
        yield 'two ids and one id' => [[self::B, self::A], [self::C], [self::B, self::A, self::C]];
        yield 'an empty other collection' => [[self::A, self::B], [], [self::A, self::B]];
        yield 'an empty collection and two ids' => [[], [self::A, self::B], [self::A, self::B]];
        yield 'two empty collections' => [[], [], []];
        yield 'an id that is in both collections' => [[self::A, self::B], [self::B], [self::A, self::B, self::B]];
    }

    public function testMergeDoesNotChangeTheTwoCollections(): void
    {
        $left = self::collection(self::A);
        $right = self::collection(self::B);

        $left->merge($right);

        self::assertSame([self::A], $left->toStringArray());
        self::assertSame([self::B], $right->toStringArray());
    }

    /**
     * @param list<string> $left
     * @param list<string> $right
     * @param list<string> $expected
     */
    #[DataProvider('provideIntersectCases')]
    public function testIntersectReturnsTheIdsThatAreInBothCollections(
        array $left,
        array $right,
        array $expected,
    ): void {
        $intersection = self::collection(...$left)->intersect(self::collection(...$right));

        self::assertSame($expected, $intersection->toStringArray());
    }

    /**
     * @return iterable<string, array{list<string>, list<string>, list<string>}>
     */
    public static function provideIntersectCases(): iterable
    {
        yield 'two common ids' => [[self::A, self::B, self::C], [self::B, self::C, self::D], [self::B, self::C]];
        yield 'the order of the left side' => [[self::C, self::B, self::A], [self::A, self::C], [self::C, self::A]];
        yield 'no common id' => [[self::A, self::B], [self::C, self::D], []];
        yield 'an empty other collection' => [[self::A, self::B], [], []];
        yield 'an empty collection' => [[], [self::A, self::B], []];
        yield 'equal collections' => [[self::A, self::B], [self::A, self::B], [self::A, self::B]];
    }

    public function testIntersectDoesNotChangeTheTwoCollections(): void
    {
        $left = self::collection(self::A, self::B);
        $right = self::collection(self::B, self::C);

        $left->intersect($right);

        self::assertSame([self::A, self::B], $left->toStringArray());
        self::assertSame([self::B, self::C], $right->toStringArray());
    }

    public function testToStringArrayReturnsTheUlidStringsInOrder(): void
    {
        $collection = IdCollection::fromArray([Id::fromString(self::B), Id::fromString(self::A)]);

        self::assertSame(['01ARYZ6S41TSV4RRFFQ69G5FA2', '01ARYZ6S41TSV4RRFFQ69G5FA1'], $collection->toStringArray());
    }

    public function testToBinaryArrayReturnsThe16ByteFormsInOrder(): void
    {
        $collection = self::collection(self::B, self::A);

        self::assertSame(
            ['01563df36481d6764c61efb99302bd42', '01563df36481d6764c61efb99302bd41'],
            array_map(bin2hex(...), $collection->toBinaryArray())
        );
    }

    private static function collection(string ...$ulids): IdCollection
    {
        return IdCollection::fromArray(array_map(Id::fromString(...), array_values($ulids)));
    }
}
