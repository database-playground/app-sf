<?php

declare(strict_types=1);

namespace App\Command;

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

#[AsCommand(
    name: 'app:export-schema',
    description: 'Export all the schema and question in the sharable format.',
)]
class ExportCommand extends Command
{
    public function __construct(
        private readonly SchemaRepository $schemaRepository,
        private readonly QuestionRepository $questionRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'The JSON filename to export the schema and questions.');
    }

    /**
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var string $filename
         */
        $filename = $input->getArgument('filename');

        /**
         * @var array<string, SchemaDto> $schemas
         */
        $schemas = [];
        foreach ($this->schemaRepository->findAll() as $schema) {
            $io->info("Exporting schema {$schema->getId()}…");
            $schemas[$schema->getId()] = SchemaDto::fromEntity($schema);
        }

        /**
         * @var QuestionDto[] $questions
         */
        $questions = [];
        foreach ($this->questionRepository->findBy(
            [],
            orderBy: ['id' => 'ASC'],
        ) as $question) {
            $io->info("Exporting question {$question->getId()}…");
            $questions[] = QuestionDto::fromEntity($question);
        }

        $io->info('Exporting schema and questions…');
        $f = fopen($filename, 'w');
        if (false === $f) {
            $io->error("Cannot open $filename for writing.");

            return Command::FAILURE;
        }

        fwrite($f, json_encode([
            'schemas' => $schemas,
            'questions' => $questions,
        ], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR));
        fclose($f);

        $io->success("Exported schema and questions to $filename.");

        return Command::SUCCESS;
    }
}
