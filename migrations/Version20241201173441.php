<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201173441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Announcement';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                CREATE TABLE announcement (
                  id VARCHAR(255) NOT NULL,
                  content TEXT NOT NULL,
                  url TEXT DEFAULT NULL,
                  published BOOLEAN NOT NULL,
                  created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                  updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                  PRIMARY KEY(id)
                )
            SQL);
        $this->addSql(<<<'SQL'
                COMMENT ON COLUMN announcement.created_at IS '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                COMMENT ON COLUMN announcement.updated_at IS '(DC2Type:datetime_immutable)'
            SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                DROP TABLE announcement
            SQL);
    }
}
