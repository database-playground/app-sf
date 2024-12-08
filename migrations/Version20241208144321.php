<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241208144321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow putting HTML content in an email';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                ALTER TABLE email ADD text_content TEXT NOT NULL DEFAULT ''
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE email RENAME COLUMN content TO html_content
            SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                ALTER TABLE email RENAME COLUMN html_content TO content
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE email DROP text_content
            SQL);
    }
}
