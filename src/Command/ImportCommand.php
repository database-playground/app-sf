<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ExportDto\QuestionDto;
use App\Entity\ExportDto\SchemaDto;
use App\Entity\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-schema',
    description: 'Import all the schema and question from the exported data.',
)]
class ImportCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'The JSON filename to import the schema and questions.');
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

        $io->info('Unmarshaling schema and questions…');
        $content = file_get_contents($filename);
        if (false === $content) {
            $io->error("Cannot read the file $filename.");

            return Command::FAILURE;
        }

        /**
         * @var \stdClass $data
         */
        $data = json_decode($content, flags: \JSON_THROW_ON_ERROR);

        if (!isset($data->schemas) || !isset($data->questions)) {
            $io->error('The schemas and questions must be set.');

            return Command::FAILURE;
        }
        if (!\is_object($data->schemas)) {
            $io->error('The schemas must be an object.');

            return Command::FAILURE;
        }
        if (!\is_array($data->questions)) {
            $io->error('The questions must be an array.');

            return Command::FAILURE;
        }

        try {
            $this->entityManager->wrapInTransaction(function (EntityManagerInterface $em) use ($io, $data): void {
                $schemaRepository = $em->getRepository(Schema::class);

                $io->info('Importing schema…');
                foreach ((array) $data->schemas as $schema) {
                    if (!\is_object($schema)) {
                        throw new \InvalidArgumentException('The schema must be an object.');
                    }

                    $dto = SchemaDto::fromJsonObject($schema);

                    $schema = $schemaRepository->find($dto->id);
                    if (null !== $schema) {
                        $io->info("Schema {$dto->id} already exists, skipping…");
                        continue;
                    }

                    $io->info("Importing schema {$dto->id}…");
                    $em->persist($dto->toEntity());
                }

                $io->info('Importing questions…');
                foreach ($data->questions as $question) {
                    if (!\is_object($question)) {
                        throw new \InvalidArgumentException('The question must be an object.');
                    }

                    $dto = QuestionDto::fromJsonObject($question);

                    $io->info("Importing question {$dto->title}…");

                    $em->persist($dto->toEntity($schemaRepository));
                }

                $em->flush();
            });
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success("Imported schema and questions from $filename.");

        return Command::SUCCESS;
    }
}
