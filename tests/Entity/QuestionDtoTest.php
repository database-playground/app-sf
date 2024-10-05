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

class QuestionDtoTest extends TestCase
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
            ->setSolutionVideo('SolutionVideoTest');

        $questionDto = QuestionDto::fromEntity($entity);

        self::assertEquals(
            (new QuestionDto())
                ->setSchemaId('1')
                ->setType('type')
                ->setDifficulty(QuestionDifficulty::Easy)
                ->setTitle('QuestionTest')
                ->setDescription('DescriptionTest')
                ->setAnswer('AnswerTest')
                ->setSolutionVideo('SolutionVideoTest'),
            $questionDto,
        );
    }

    /**
     * @throws Exception
     */
    public function testToEntity(): void
    {
        $schemaRepository = $this->createMock(SchemaRepository::class);
        $schemaRepository
            ->method('find')
            ->willReturn((new Schema())->setId('1'));

        $questionDto = (new QuestionDto())
            ->setSchemaId('1')
            ->setType('type')
            ->setDifficulty(QuestionDifficulty::Easy)
            ->setTitle('QuestionTest')
            ->setDescription('DescriptionTest')
            ->setAnswer('AnswerTest')
            ->setSolutionVideo('SolutionVideoTest');

        $entity = $questionDto->toEntity($schemaRepository);

        self::assertEquals(
            (new Question())
                ->setSchema((new Schema())->setId('1'))
                ->setType('type')
                ->setDifficulty(QuestionDifficulty::Easy)
                ->setTitle('QuestionTest')
                ->setDescription('DescriptionTest')
                ->setAnswer('AnswerTest')
                ->setSolutionVideo('SolutionVideoTest'),
            $entity,
        );
    }
}
