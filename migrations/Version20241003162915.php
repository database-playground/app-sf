<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003162915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Login Event';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE login_event (id UUID NOT NULL, account_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3DDECD339B6B5FBA ON login_event (account_id)');
        $this->addSql('COMMENT ON COLUMN login_event.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN login_event.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE login_event ADD CONSTRAINT FK_3DDECD339B6B5FBA FOREIGN KEY (account_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE login_event DROP CONSTRAINT FK_3DDECD339B6B5FBA');
        $this->addSql('DROP TABLE login_event');
    }
}
