<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ExportDto\QuestionDto;
use App\Entity\ExportDto\SchemaDto;
use App\Repository\QuestionRepository;
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

        $filename = $input->getArgument('filename');
        if (!\is_string($filename)) {
            $io->error('The filename must be a string.');

            return Command::FAILURE;
        }

        /**
         * @var array<string, SchemaDto> $schemas
         */
        $schemas = [];

        /**
         * @var QuestionDto[] $questions
         */
        $questions = [];

        $io->info('Querying schema and questions…');
        $questionsFromDatabase = $this->questionRepository->findBy(
            [],
            orderBy: ['id' => 'ASC'],
        );

        foreach ($questionsFromDatabase as $question) {
            $schema = $question->getSchema();
            if ($schema && !isset($schemas[$schema->getId()])) {
                $io->info("Exporting schema {$schema->getId()}…");
                $schemas[$schema->getId()] = SchemaDto::fromEntity($schema);
            }

            $io->info("Exporting question {$question->getId()}…");
            $questions[] = QuestionDto::fromEntity($question);
        }

        $io->info('Exporting schema and questions…');
        $f = fopen($filename, 'w');
        if (!$f) {
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
