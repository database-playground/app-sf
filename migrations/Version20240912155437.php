<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912155437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE solution_video_event (id UUID NOT NULL, opener_id INT NOT NULL, question_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AC66259F2B174A2B ON solution_video_event (opener_id)');
        $this->addSql('CREATE INDEX IDX_AC66259F1E27F6BF ON solution_video_event (question_id)');
        $this->addSql('COMMENT ON COLUMN solution_video_event.id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE solution_video_event ADD CONSTRAINT FK_AC66259F2B174A2B FOREIGN KEY (opener_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE solution_video_event ADD CONSTRAINT FK_AC66259F1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE solution_video_event DROP CONSTRAINT FK_AC66259F2B174A2B');
        $this->addSql('ALTER TABLE solution_video_event DROP CONSTRAINT FK_AC66259F1E27F6BF');
        $this->addSql('DROP TABLE solution_video_event');
    }
}
