<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241202060920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Email Event';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                CREATE TABLE email_event (
                  id UUID NOT NULL,
                  to_user_id INT DEFAULT NULL,
                  created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                  to_address VARCHAR(512) NOT NULL,
                  subject VARCHAR(4096) NOT NULL,
                  content TEXT NOT NULL,
                  PRIMARY KEY(id)
                )
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_A6E34B2829F6EE60 ON email_event (to_user_id)
            SQL);
        $this->addSql(<<<'SQL'
                COMMENT ON COLUMN email_event.id IS '(DC2Type:ulid)'
            SQL);
        $this->addSql(<<<'SQL'
                COMMENT ON COLUMN email_event.created_at IS '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  email_event
                ADD
                  CONSTRAINT FK_A6E34B2829F6EE60 FOREIGN KEY (to_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                ALTER TABLE email_event DROP CONSTRAINT FK_A6E34B2829F6EE60
            SQL);
        $this->addSql(<<<'SQL'
                DROP TABLE email_event
            SQL);
    }
}
