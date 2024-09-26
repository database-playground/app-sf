<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240925063543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'HintOpen event';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hint_open_event (id UUID NOT NULL, opener_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, response TEXT NOT NULL, query TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D369FDBA2B174A2B ON hint_open_event (opener_id)');
        $this->addSql('COMMENT ON COLUMN hint_open_event.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN hint_open_event.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE hint_open_event ADD CONSTRAINT FK_D369FDBA2B174A2B FOREIGN KEY (opener_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hint_open_event DROP CONSTRAINT FK_D369FDBA2B174A2B');
        $this->addSql('DROP TABLE hint_open_event');
    }
}
