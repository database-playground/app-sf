<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ExportDto\SchemaDto;
use App\Entity\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SchemaDtoTest extends TestCase
{
    public function testEntityToDto(): void
    {
        $entity = (new Schema())
            ->setId('SchemaId')
            ->setPicture('PictureTest')
            ->setDescription('DescriptionTest')
            ->setSchema('SchemaTest')
        ;

        $schemaDto = SchemaDto::fromEntity($entity);

        self::assertSame(
            (new SchemaDto())
                ->setId('SchemaId')
                ->setPicture('PictureTest')
                ->setDescription('DescriptionTest')
                ->setSchema('SchemaTest'),
            $schemaDto,
        );
    }

    public function testEntityToDtoWithoutPicture(): void
    {
        $entity = (new Schema())
            ->setId('SchemaId')
            ->setDescription('DescriptionTest')
            ->setSchema('SchemaTest')
        ;

        $schemaDto = SchemaDto::fromEntity($entity);

        self::assertSame(
            (new SchemaDto())
                ->setId('SchemaId')
                ->setPicture(null)
                ->setDescription('DescriptionTest')
                ->setSchema('SchemaTest'),
            $schemaDto,
        );
    }

    public function testToEntity(): void
    {
        $schemaDto = (new SchemaDto())
            ->setId('SchemaId')
            ->setPicture('PictureTest')
            ->setDescription('DescriptionTest')
            ->setSchema('SchemaTest')
        ;

        $schema = $schemaDto->toEntity();

        self::assertSame('SchemaId', $schema->getId());
        self::assertSame('PictureTest', $schema->getPicture());
        self::assertSame('DescriptionTest', $schema->getDescription());
        self::assertSame('SchemaTest', $schema->getSchema());
    }
}
