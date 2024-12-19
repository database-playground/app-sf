<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ExportDto\ExportedDataDto;
use App\Entity\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:import-schema',
    description: 'Import all the schema and question from the exported data.',
)]
class ImportCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'The JSON filename to import the schema and questions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var string $filename
         */
        $filename = $input->getArgument('filename');

        $io->info('Unmarshaling schema and questions…');
        $content = file_get_contents($filename);
        if (false === $content) {
            $io->error("Cannot read the file {$filename}.");

            return Command::FAILURE;
        }

        $exportedData = $this->serializer->deserialize($content, ExportedDataDto::class, 'json');

        $this->entityManager->wrapInTransaction(static function (EntityManagerInterface $em) use ($io, $exportedData): void {
            $schemaRepository = $em->getRepository(Schema::class);

            $io->info('Importing schema…');
            foreach ($exportedData->getSchemas() as $schema) {
                $existingSchema = $schemaRepository->find($schema->getId());
                if (null !== $existingSchema) {
                    $io->info("Schema {$schema->getId()} already exists, skipping…");

                    continue;
                }

                $io->info("Importing schema {$schema->getId()}…");
                $em->persist($schema->toEntity());
            }

            $io->info('Importing questions…');
            foreach ($exportedData->getQuestions() as $question) {
                $io->info("Importing question {$question->getTitle()}…");

                $em->persist($question->toEntity($schemaRepository));
            }

            $em->flush();
        });

        $io->success("Imported schema and questions from {$filename}.");

        return Command::SUCCESS;
    }
}
