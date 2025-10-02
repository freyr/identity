<?php

declare(strict_types=1);

namespace Freyr\Identity\Examples;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Freyr\Identity\Id;

/**
 * Example Doctrine custom type for storing Id as binary UUID v7 in the database.
 *
 * This example demonstrates compatibility with the Id class contract.
 * The Id class provides all necessary methods:
 * - Id::fromBinary(string): static  -> for hydration from database
 * - toBinary(): string              -> for persistence to database
 *
 * Installation:
 * 1. composer require doctrine/dbal
 * 2. Copy this file to your project (e.g., src/Infrastructure/Doctrine/Type/IdType.php)
 * 3. Register the type:
 *    Type::addType('freyr_identity', IdType::class);
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
 *     id BINARY(16) NOT NULL PRIMARY KEY COMMENT '(DC2Type:freyr_identity)'
 * );
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

    /**
     * Converts database binary value to Id object.
     * Uses Id::fromBinary() - compatible with existing contract.
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Id
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

        // Uses public static factory method - no private property access needed
        return Id::fromBinary($value);
    }

    /**
     * Converts Id object to database binary value.
     * Uses toBinary() method - compatible with existing contract.
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Id) {
            throw new \InvalidArgumentException('Expected Id instance, got ' . get_debug_type($value));
        }

        // Uses public method - no private property access needed
        return $value->toBinary();
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
