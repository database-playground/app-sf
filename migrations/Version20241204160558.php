<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241204160558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Email and Email Delivery Event';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                CREATE SEQUENCE email_id_seq INCREMENT BY 1 MINVALUE 1 START 1
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE email (
                  id INT NOT NULL,
                  subject VARCHAR(4096) NOT NULL,
                  content TEXT NOT NULL,
                  kind VARCHAR(255) NOT NULL,
                  PRIMARY KEY(id)
                )
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE email_delivery_event (
                  id UUID NOT NULL,
                  to_user_id INT DEFAULT NULL,
                  email_id INT NOT NULL,
                  created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                  to_address VARCHAR(512) NOT NULL,
                  PRIMARY KEY(id)
                )
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_F35AF0FC29F6EE60 ON email_delivery_event (to_user_id)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_F35AF0FCA832C1C9 ON email_delivery_event (email_id)
            SQL);
        $this->addSql(<<<'SQL'
                COMMENT ON COLUMN email_delivery_event.id IS '(DC2Type:ulid)'
            SQL);
        $this->addSql(<<<'SQL'
                COMMENT ON COLUMN email_delivery_event.created_at IS '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  email_delivery_event
                ADD
                  CONSTRAINT FK_F35AF0FC29F6EE60 FOREIGN KEY (to_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  email_delivery_event
                ADD
                  CONSTRAINT FK_F35AF0FCA832C1C9 FOREIGN KEY (email_id) REFERENCES email (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                DROP SEQUENCE email_id_seq CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE email_delivery_event DROP CONSTRAINT FK_F35AF0FC29F6EE60
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE email_delivery_event DROP CONSTRAINT FK_F35AF0FCA832C1C9
            SQL);
        $this->addSql(<<<'SQL'
                DROP TABLE email
            SQL);
        $this->addSql(<<<'SQL'
                DROP TABLE email_delivery_event
            SQL);
    }
}
