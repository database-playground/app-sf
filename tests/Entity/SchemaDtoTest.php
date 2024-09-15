<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ExportDto\QuestionDto;
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

    public function testDtoFromJson(): void
    {
        $json = (object) [
            'id' => 'SchemaId',
            'picture' => 'PictureTest',
            'description' => 'DescriptionTest',
            'schema' => 'SchemaTest',
        ];

        $schemaDto = SchemaDto::fromJsonObject($json);

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

    public function testDtoFromJsonOptional(): void
    {
        $json = (object) [
            'id' => 'SchemaId',
            'description' => 'DescriptionTest',
            'schema' => 'SchemaTest',
        ];

        $schemaDto = SchemaDto::fromJsonObject($json);

        $this->assertEquals(
            new SchemaDto(
                id: 'SchemaId',
                picture: null,
                description: 'DescriptionTest',
                schema: 'SchemaTest',
            ),
            $schemaDto,
        );
        $this->assertNotTrue(isset($json->picture));
    }

    public function testDtoFromJsonInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        QuestionDto::fromJsonObject((object) []);
    }

    public function testToEntity(): void
    {
        $schemaDto = new SchemaDto(
            id: 'SchemaId',
            picture: 'PictureTest',
            description: 'DescriptionTest',
            schema: 'SchemaTest',
        );

        $schema = $schemaDto->toEntity();

        $this->assertEquals('SchemaId', $schema->getId());
        $this->assertEquals('PictureTest', $schema->getPicture());
        $this->assertEquals('DescriptionTest', $schema->getDescription());
        $this->assertEquals('SchemaTest', $schema->getSchema());
    }
}