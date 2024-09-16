<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Ulid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240916035156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'created_at columns for events';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment_like_event ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN comment_like_event.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE solution_event ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN solution_event.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE solution_video_event ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN solution_video_event.created_at IS \'(DC2Type:datetime_immutable)\'');

        // Set the created_at column to the id value for all existing rows
        $eventTables = ['comment_like_event', 'solution_event', 'solution_video_event'];
        foreach ($eventTables as $eventTable) {
            $rows = $this->connection->fetchAllAssociative('SELECT id FROM '.$eventTable, types: [
                'id' => Ulid::class,
            ]);

            foreach ($rows as $row) {
                \assert(\is_string($row['id']));
                $ulid = Ulid::fromRfc4122($row['id']);

                $this->addSql('UPDATE '.$eventTable.' SET created_at = :createdAt WHERE id = :id', [
                    'createdAt' => $ulid->getDateTime()->format('Y-m-d H:i:s'),
                    'id' => $ulid->toRfc4122(),
                ]);
            }
        }

        // mark NOT NULL for created_at columns
        $this->addSql('ALTER TABLE comment_like_event ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE solution_event ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE solution_video_event ALTER created_at SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE comment_like_event DROP created_at');
        $this->addSql('ALTER TABLE solution_event DROP created_at');
        $this->addSql('ALTER TABLE solution_video_event DROP created_at');
    }
}
