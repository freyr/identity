<?php

declare(strict_types=1);

namespace Freyr\Identity\Examples;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Freyr\Identity\Id;

/**
 * Example Doctrine DBAL 4 type that stores an Id as 16 bytes.
 *
 * The type uses only the public contract of Id:
 * - Id::fromBinary(string): static  -> from the database value
 * - toBinary(): string              -> to the database value
 *
 * Installation:
 * 1. composer require doctrine/dbal:^4.0
 * 2. Copy this file to your project (e.g., src/Infrastructure/Doctrine/Type/IdType.php)
 * 3. Register the type:
 *    Type::addType(DoctrineIdType::NAME, DoctrineIdType::class);
 * 4. Use in your entities:
 *
 *    use Doctrine\ORM\Mapping as ORM;
 *
 *    #[ORM\Entity]
 *    class User
 *    {
 *        #[ORM\Id]
 *        #[ORM\Column(type: 'freyr_identity', unique: true)]
 *        private Id $id;
 *
 *        public function __construct()
 *        {
 *            $this->id = Id::new();
 *        }
 *    }
 *
 * Database schema (MySQL):
 * CREATE TABLE user (
 *     id BINARY(16) NOT NULL PRIMARY KEY
 * );
 *
 * This type always creates the base class Id. An Id from the database is the same as a UserId
 * with the same ULID (sameAs()), but it is not equal to it (equalTo() also compares the class).
 * For a typed identifier, copy this class for each subclass and call UserId::fromBinary().
 */
final class DoctrineIdType extends Type
{
    public const NAME = 'freyr_identity';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL([
            'length' => 16,
            'fixed' => true,
        ]);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Id
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Id) {
            return $value;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string for Id conversion, got ' . get_debug_type($value));
        }

        return Id::fromBinary($value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Id) {
            throw new \InvalidArgumentException('Expected Id instance, got ' . get_debug_type($value));
        }

        return $value->toBinary();
    }
}
