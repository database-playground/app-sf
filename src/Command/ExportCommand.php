<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ExportDto\ExportedDataDto;
use App\Entity\ExportDto\QuestionDto;
use App\Entity\ExportDto\SchemaDto;
use App\Repository\QuestionRepository;
use App\Repository\SchemaRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:export-schema',
    description: 'Export all the schema and question in the sharable format.',
)]
class ExportCommand extends Command
{
    public function __construct(
        private readonly SchemaRepository $schemaRepository,
        private readonly QuestionRepository $questionRepository,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'The JSON filename to export the schema and questions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var string $filename
         */
        $filename = $input->getArgument('filename');

        $exportedData = new ExportedDataDto();

        /**
         * @var array<string, SchemaDto> $schemas
         */
        $schemas = [];
        foreach ($this->schemaRepository->findAll() as $schema) {
            $io->info("Exporting schema {$schema->getId()}…");
            $schemas[$schema->getId()] = SchemaDto::fromEntity($schema);
        }
        $exportedData->setSchemas($schemas);

        /**
         * @var list<QuestionDto> $questions
         */
        $questions = [];
        foreach ($this->questionRepository->findBy(
            [],
            orderBy: ['id' => 'ASC'],
        ) as $question) {
            $io->info("Exporting question {$question->getId()}…");
            $questions[] = QuestionDto::fromEntity($question);
        }
        $exportedData->setQuestions($questions);

        $io->info('Exporting schema and questions…');
        $serialized = $this->serializer->serialize($exportedData, 'json', [
            'json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
        ]);

        if (false === file_put_contents($filename, $serialized)) {
            $io->error("Cannot write to the file {$filename}.");

            return Command::FAILURE;
        }

        $io->success("Exported schema and questions to {$filename}.");

        return Command::SUCCESS;
    }
}
