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
            new QuestionDto(
                schemaId: '1',
                type: 'type',
                difficulty: QuestionDifficulty::Easy,
                title: 'QuestionTest',
                description: 'DescriptionTest',
                answer: 'AnswerTest',
                solutionVideo: 'SolutionVideoTest',
            ),
            $questionDto,
        );
    }

    public function testDtoToJson(): void
    {
        $questionDto = new QuestionDto(
            schemaId: '1',
            type: 'type',
            difficulty: QuestionDifficulty::Easy,
            title: 'QuestionTest',
            description: 'DescriptionTest',
            answer: 'AnswerTest',
            solutionVideo: 'SolutionVideoTest',
        );

        self::assertEquals(
            json_encode([
                'schemaId' => '1',
                'type' => 'type',
                'difficulty' => QuestionDifficulty::Easy->value,
                'title' => 'QuestionTest',
                'description' => 'DescriptionTest',
                'answer' => 'AnswerTest',
                'solutionVideo' => 'SolutionVideoTest',
            ]),
            json_encode($questionDto),
        );
    }

    public function testDtoFromJson(): void
    {
        $json = (object) [
            'schemaId' => '1',
            'type' => 'type',
            'difficulty' => QuestionDifficulty::Easy->value,
            'title' => 'QuestionTest',
            'description' => 'DescriptionTest',
            'answer' => 'AnswerTest',
            'solutionVideo' => 'SolutionVideoTest',
        ];

        $questionDto = QuestionDto::fromJsonObject($json);

        self::assertEquals(
            new QuestionDto(
                schemaId: '1',
                type: 'type',
                difficulty: QuestionDifficulty::Easy,
                title: 'QuestionTest',
                description: 'DescriptionTest',
                answer: 'AnswerTest',
                solutionVideo: 'SolutionVideoTest',
            ),
            $questionDto,
        );
    }

    public function testDtoFromJsonInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        QuestionDto::fromJsonObject((object) []);
    }

    public function testDtoFromJsonOptional(): void
    {
        $o = (object) [
            'schemaId' => '1',
            'type' => 'type',
            'title' => 'QuestionTest',
            'difficulty' => 'Hi',
            'answer' => 'AnswerTest',
            'solutionVideo' => 'SolutionVideoTest',
        ];

        $dto = QuestionDto::fromJsonObject($o);

        self::assertEquals(QuestionDifficulty::Unspecified, $dto->difficulty);
        self::assertNull($dto->description);
        self::assertNotTrue(isset($o->description));
    }

    public function testDtoFromJsonInvalidDifficulty(): void
    {
        $dto = QuestionDto::fromJsonObject((object) [
            'schemaId' => '1',
            'type' => 'type',
            'difficulty' => 'invalid',
            'title' => 'QuestionTest',
            'description' => 'DescriptionTest',
            'answer' => 'AnswerTest',
            'solutionVideo' => 'SolutionVideoTest',
        ]);

        self::assertEquals(QuestionDifficulty::Unspecified, $dto->difficulty);
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

        $questionDto = new QuestionDto(
            schemaId: '1',
            type: 'type',
            difficulty: QuestionDifficulty::Easy,
            title: 'QuestionTest',
            description: 'DescriptionTest',
            answer: 'AnswerTest',
            solutionVideo: 'SolutionVideoTest',
        );

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
