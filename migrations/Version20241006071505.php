<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241006071505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Feedback Entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE feedback (id UUID NOT NULL, sender_id INT DEFAULT NULL, title TEXT NOT NULL, description TEXT NOT NULL, type VARCHAR(255) NOT NULL, metadata JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, contact TEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D2294458F624B39D ON feedback (sender_id)');
        $this->addSql('CREATE INDEX IDX_D22944588CDE5729 ON feedback (type)');
        $this->addSql('CREATE INDEX IDX_D2294458F624B39D8CDE5729 ON feedback (sender_id, type)');
        $this->addSql('CREATE INDEX IDX_D22944587B00651C ON feedback (status)');
        $this->addSql('COMMENT ON COLUMN feedback.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN feedback.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN feedback.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D2294458F624B39D FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE feedback DROP CONSTRAINT FK_D2294458F624B39D');
        $this->addSql('DROP TABLE feedback');
    }
}
