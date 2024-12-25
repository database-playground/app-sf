<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ExportDto\QuestionDto;
use App\Entity\Question;
use App\Entity\QuestionDifficulty;
use App\Entity\Schema;
use App\Repository\SchemaRepository;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class QuestionDtoTest extends TestCase
{
    public function testEntityToDto(): void
    {
        $entity = (new Question())
            ->setSchema((new Schema())->setId('1'))
            ->setType('type')
            ->setDifficulty(QuestionDifficulty::Easy)
            ->setTitle('QuestionTest')
            ->setDescription('DescriptionTest')
            ->setAnswer('AnswerTest')
            ->setSolutionVideo('SolutionVideoTest')
        ;

        $questionDto = QuestionDto::fromEntity($entity);

        self::assertSame('1', $questionDto->getSchemaId());
        self::assertSame('type', $questionDto->getType());
        self::assertSame(QuestionDifficulty::Easy, $questionDto->getDifficulty());
        self::assertSame('QuestionTest', $questionDto->getTitle());
        self::assertSame('DescriptionTest', $questionDto->getDescription());
        self::assertSame('AnswerTest', $questionDto->getAnswer());
        self::assertSame('SolutionVideoTest', $questionDto->getSolutionVideo());
    }

    /**
     * @throws Exception
     */
    public function testToEntity(): void
    {
        $schemaRepository = $this->createMock(SchemaRepository::class);
        $schemaRepository
            ->method('find')
            ->willReturn((new Schema())->setId('1'))
        ;

        $questionDto = (new QuestionDto())
            ->setSchemaId('1')
            ->setType('type')
            ->setDifficulty(QuestionDifficulty::Easy)
            ->setTitle('QuestionTest')
            ->setDescription('DescriptionTest')
            ->setAnswer('AnswerTest')
            ->setSolutionVideo('SolutionVideoTest')
        ;

        $entity = $questionDto->toEntity($schemaRepository);

        self::assertSame('1', $entity->getSchema()->getId());
        self::assertSame('type', $entity->getType());
        self::assertSame(QuestionDifficulty::Easy, $entity->getDifficulty());
        self::assertSame('QuestionTest', $entity->getTitle());
        self::assertSame('DescriptionTest', $entity->getDescription());
        self::assertSame('AnswerTest', $entity->getAnswer());
        self::assertSame('SolutionVideoTest', $entity->getSolutionVideo());
    }
}
