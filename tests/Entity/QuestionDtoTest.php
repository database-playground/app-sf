<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ExportDto\QuestionDto;
use App\Entity\Question;
use App\Entity\QuestionDifficulty;
use App\Entity\Schema;
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

        $this->assertEquals(
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

        $this->assertEquals(
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
}
