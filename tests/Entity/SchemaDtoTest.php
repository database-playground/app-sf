<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ExportDto\SchemaDto;
use App\Entity\Schema;
use PHPUnit\Framework\TestCase;

class SchemaDtoTest extends TestCase
{
    public function testEntityToDto(): void
    {
        $entity = (new Schema())
            ->setId('SchemaId')
            ->setPicture('PictureTest')
            ->setDescription('DescriptionTest')
            ->setSchema('SchemaTest');

        $schemaDto = SchemaDto::fromEntity($entity);

        $this->assertEquals(
            new SchemaDto(
                id: 'SchemaId',
                picture: 'PictureTest',
                description: 'DescriptionTest',
                schema: 'SchemaTest',
            ),
            $schemaDto,
        );
    }

    public function testEntityToDtoWithoutPicture(): void
    {
        $entity = (new Schema())
            ->setId('SchemaId')
            ->setDescription('DescriptionTest')
            ->setSchema('SchemaTest');

        $schemaDto = SchemaDto::fromEntity($entity);

        $this->assertEquals(
            new SchemaDto(
                id: 'SchemaId',
                picture: null,
                description: 'DescriptionTest',
                schema: 'SchemaTest',
            ),
            $schemaDto,
        );
    }

    public function testDtoToJson(): void
    {
        $schemaDto = new SchemaDto(
            id: 'SchemaId',
            picture: 'PictureTest',
            description: 'DescriptionTest',
            schema: 'SchemaTest',
        );

        $this->assertEquals(
            json_encode([
                'id' => 'SchemaId',
                'picture' => 'PictureTest',
                'description' => 'DescriptionTest',
                'schema' => 'SchemaTest',
            ]),
            json_encode($schemaDto),
        );
    }

    public function testDtoToJsonWithoutPicture(): void
    {
        $schemaDto = new SchemaDto(
            id: 'SchemaId',
            picture: null,
            description: 'DescriptionTest',
            schema: 'SchemaTest',
        );

        $this->assertEquals(
            json_encode([
                'id' => 'SchemaId',
                'picture' => null,
                'description' => 'DescriptionTest',
                'schema' => 'SchemaTest',
            ]),
            json_encode($schemaDto),
        );
    }
}
