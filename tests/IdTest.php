<?php

declare(strict_types=1);

namespace Freyr\Identity\Tests;

use Freyr\Identity\Id;
use PHPUnit\Framework\TestCase;

final class IdTest extends TestCase
{
    public function testNewReturnsIdInstance(): void
    {
        $id = Id::new();

        self::assertInstanceOf(Id::class, $id);
    }

    public function testTwoNewCallsProduceDifferentIds(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        self::assertFalse($id1->sameAs($id2));
    }

    public function testToStringReturnsCrockfordBase32Format(): void
    {
        $id = Id::new();
        $string = (string) $id;

        self::assertSame(26, strlen($string));
        self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $string);
    }

    public function testToBinaryReturns16Bytes(): void
    {
        $id = Id::new();

        self::assertSame(16, strlen($id->toBinary()));
    }

    public function testStringRoundTrip(): void
    {
        $id = Id::new();
        $restored = Id::fromString((string) $id);

        self::assertTrue($id->sameAs($restored));
    }

    public function testBinaryRoundTrip(): void
    {
        $id = Id::new();
        $restored = Id::fromBinary($id->toBinary());

        self::assertTrue($id->sameAs($restored));
    }

    public function testFromStringWithValidUlid(): void
    {
        $id = Id::fromString('01ARYZ6S41TSV4RRFFQ69G5FAV');

        self::assertSame('01ARYZ6S41TSV4RRFFQ69G5FAV', (string) $id);
    }

    public function testFromStringWithUuidFormatAutoConverts(): void
    {
        $id = Id::fromString('01920a7c-8b00-7000-8000-000000000001');

        self::assertSame(26, strlen((string) $id));
        self::assertSame(16, strlen($id->toBinary()));

        $restored = Id::fromString((string) $id);
        self::assertTrue($id->sameAs($restored));
    }

    public function testFromStringWithEmptyStringThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Id::fromString('');
    }

    public function testFromStringWithInvalidInputThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Id::fromString('not-a-valid-id');
    }

    public function testFromBinaryWithInvalidLengthThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Id::fromBinary('short');
    }

    public function testFromStringRejectsNilUlid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Id::fromString('00000000000000000000000000');
    }

    public function testFromStringRejectsMaxUlid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Id::fromString('7ZZZZZZZZZZZZZZZZZZZZZZZZZ');
    }

    public function testFromBinaryRejectsNilBinary(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Id::fromBinary(str_repeat("\x00", 16));
    }

    public function testFromBinaryRejectsMaxBinary(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Id::fromBinary(str_repeat("\xFF", 16));
    }

    public function testSameAsReturnsTrueForEqualIds(): void
    {
        $string = '01ARYZ6S41TSV4RRFFQ69G5FAV';
        $id1 = Id::fromString($string);
        $id2 = Id::fromString($string);

        self::assertTrue($id1->sameAs($id2));
    }

    public function testSameAsReturnsFalseForDifferentIds(): void
    {
        $id1 = Id::new();
        $id2 = Id::new();

        self::assertFalse($id1->sameAs($id2));
    }

    public function testSubclassFactoryReturnsSubclassInstance(): void
    {
        $userId = UserIdStub::new();

        self::assertInstanceOf(UserIdStub::class, $userId);
    }

    public function testSubclassRoundTrip(): void
    {
        $userId = UserIdStub::new();
        $restored = UserIdStub::fromString((string) $userId);

        self::assertInstanceOf(UserIdStub::class, $restored);
        self::assertTrue($userId->sameAs($restored));
    }
}

/**
 * Test stub for subclass behavior.
 */
class UserIdStub extends Id {}
