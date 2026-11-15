<?php

declare(strict_types=1);

namespace Freyr\Identity\Tests;

use Freyr\Identity\Id;
use Freyr\Identity\Tests\Fixtures\OrderId;
use Freyr\Identity\Tests\Fixtures\UserId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IdTest extends TestCase
{
    /**
     * One identifier in its three forms. The hex and UUID forms were calculated by hand from the ULID.
     */
    private const ULID = '01ARYZ6S41TSV4RRFFQ69G5FAV';

    private const HEX = '01563df36481d6764c61efb99302bd5b';

    private const UUID = '01563df3-6481-d676-4c61-efb99302bd5b';

    private const OTHER_ULID = '01ARYZ6S41TSV4RRFFQ69G5FAW';

    private const NIL_AND_MAX_MESSAGE = '/^NIL and MAX ULIDs are not valid identifiers\.$/';

    public function testNewCreatesA26CharacterCrockfordBase32String(): void
    {
        self::assertMatchesRegularExpression('/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/', (string) Id::new());
    }

    public function testNewCreatesUniqueIdsInAscendingOrder(): void
    {
        $ids = [];
        for ($i = 0; $i < 1000; ++$i) {
            $ids[] = (string) Id::new();
        }

        $expected = array_unique($ids);
        sort($expected, SORT_STRING);

        self::assertSame($expected, $ids);
    }

    #[DataProvider('provideAcceptedStringFormats')]
    public function testFromStringConvertsEachAcceptedFormatToTheSameUlid(string $input): void
    {
        self::assertSame(self::ULID, (string) Id::fromString($input));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideAcceptedStringFormats(): iterable
    {
        yield 'a ULID' => [self::ULID];
        yield 'a ULID in lowercase' => ['01aryz6s41tsv4rrffq69g5fav'];
        yield 'a UUID' => [self::UUID];
        yield 'a UUID in uppercase' => ['01563DF3-6481-D676-4C61-EFB99302BD5B'];
        yield 'a base58 string' => ['1AaLsH1SUNAUK3dNDbnPwY'];
        yield '16 bytes' => ["\x01\x56\x3d\xf3\x64\x81\xd6\x76\x4c\x61\xef\xb9\x93\x02\xbd\x5b"];
    }

    #[DataProvider('provideInvalidStrings')]
    public function testFromStringRejectsAStringThatIsNotAnIdentifier(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^Invalid ULID/');

        Id::fromString($input);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidStrings(): iterable
    {
        yield 'an empty string' => [''];
        yield 'text' => ['not-a-valid-id'];
        yield '25 characters' => ['01ARYZ6S41TSV4RRFFQ69G5FA'];
        yield 'a ULID above the maximum' => ['8ZZZZZZZZZZZZZZZZZZZZZZZZZ'];
        yield '32 hex characters' => [self::HEX];
    }

    #[DataProvider('provideNilAndMaxStrings')]
    public function testFromStringRejectsTheNilAndMaxValues(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches(self::NIL_AND_MAX_MESSAGE);

        Id::fromString($input);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNilAndMaxStrings(): iterable
    {
        yield 'the NIL ULID' => ['00000000000000000000000000'];
        yield 'the MAX ULID' => ['7ZZZZZZZZZZZZZZZZZZZZZZZZZ'];
        yield 'the NIL UUID' => ['00000000-0000-0000-0000-000000000000'];
        yield 'the MAX UUID' => ['ffffffff-ffff-ffff-ffff-ffffffffffff'];
    }

    public function testFromBinaryCreatesTheIdOfThe16Bytes(): void
    {
        $id = Id::fromBinary("\x01\x56\x3d\xf3\x64\x81\xd6\x76\x4c\x61\xef\xb9\x93\x02\xbd\x5b");

        self::assertSame(self::ULID, (string) $id);
    }

    #[DataProvider('provideBinaryStringsOfTheWrongLength')]
    public function testFromBinaryRejectsAStringThatIsNot16Bytes(string $bytes): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^Invalid binary uid provided\.$/');

        Id::fromBinary($bytes);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideBinaryStringsOfTheWrongLength(): iterable
    {
        yield 'an empty string' => [''];
        yield '15 bytes' => [str_repeat('a', 15)];
        yield '17 bytes' => [str_repeat('a', 17)];
    }

    #[DataProvider('provideNilAndMaxBytes')]
    public function testFromBinaryRejectsTheNilAndMaxValues(string $bytes): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches(self::NIL_AND_MAX_MESSAGE);

        Id::fromBinary($bytes);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNilAndMaxBytes(): iterable
    {
        yield '16 zero bytes' => [str_repeat("\x00", 16)];
        yield '16 bytes of 0xFF' => [str_repeat("\xFF", 16)];
    }

    public function testToBinaryReturnsThe16BytesOfTheUlid(): void
    {
        self::assertSame(self::HEX, bin2hex(Id::fromString(self::ULID)->toBinary()));
    }

    public function testSameAsReturnsTrueForTwoIdsWithTheSameValue(): void
    {
        self::assertTrue(Id::fromString(self::ULID)->sameAs(Id::fromString(self::UUID)));
    }

    public function testSameAsReturnsFalseForTwoIdsWithDifferentValues(): void
    {
        self::assertFalse(Id::fromString(self::ULID)->sameAs(Id::fromString(self::OTHER_ULID)));
    }

    public function testSameAsReturnsTrueForDifferentSubclassesWithTheSameValue(): void
    {
        $userId = UserId::fromString(self::ULID);
        $orderId = OrderId::fromString(self::ULID);

        self::assertTrue($userId->sameAs($orderId));
    }

    public function testSameAsReturnsTrueBetweenTheBaseClassAndASubclassWithTheSameValue(): void
    {
        $id = Id::fromString(self::ULID);
        $userId = UserId::fromString(self::ULID);

        self::assertTrue($userId->sameAs($id));
        self::assertTrue($id->sameAs($userId));
    }

    public function testEqualToReturnsTrueForTwoBaseClassIdsWithTheSameValue(): void
    {
        self::assertTrue(Id::fromString(self::ULID)->equalTo(Id::fromString(self::ULID)));
    }

    public function testEqualToReturnsTrueForTheSameSubclassAndTheSameValue(): void
    {
        self::assertTrue(UserId::fromString(self::ULID)->equalTo(UserId::fromString(self::ULID)));
    }

    public function testEqualToReturnsFalseForTheSameSubclassAndADifferentValue(): void
    {
        self::assertFalse(UserId::fromString(self::ULID)->equalTo(UserId::fromString(self::OTHER_ULID)));
    }

    public function testEqualToReturnsFalseForADifferentSubclassAndTheSameValue(): void
    {
        $userId = UserId::fromString(self::ULID);
        $orderId = OrderId::fromString(self::ULID);

        self::assertFalse($userId->equalTo($orderId));
        self::assertFalse($orderId->equalTo($userId));
    }

    public function testEqualToReturnsFalseBetweenTheBaseClassAndASubclass(): void
    {
        $id = Id::fromString(self::ULID);
        $userId = UserId::fromString(self::ULID);

        self::assertFalse($id->equalTo($userId));
        self::assertFalse($userId->equalTo($id));
    }

    public function testTheConstructorIsPrivateAndFinal(): void
    {
        $constructor = new \ReflectionMethod(Id::class, '__construct');

        self::assertTrue($constructor->isPrivate());
        self::assertTrue($constructor->isFinal());
    }
}
