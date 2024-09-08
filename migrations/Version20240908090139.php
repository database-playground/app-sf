<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240908090139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add solution_event table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE solution_event (id UUID NOT NULL, submitter_id INT NOT NULL, question_id INT NOT NULL, status VARCHAR(255) NOT NULL, query TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_116FEF20919E5513 ON solution_event (submitter_id)');
        $this->addSql('CREATE INDEX IDX_116FEF201E27F6BF ON solution_event (question_id)');
        $this->addSql('COMMENT ON COLUMN solution_event.id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE solution_event ADD CONSTRAINT FK_116FEF20919E5513 FOREIGN KEY (submitter_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE solution_event ADD CONSTRAINT FK_116FEF201E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE solution_event DROP CONSTRAINT FK_116FEF20919E5513');
        $this->addSql('ALTER TABLE solution_event DROP CONSTRAINT FK_116FEF201E27F6BF');
        $this->addSql('DROP TABLE solution_event');
    }
}
