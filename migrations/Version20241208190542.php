<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241208190542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop default value of the required text_content column';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                ALTER TABLE email ALTER text_content DROP DEFAULT
            SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                CREATE SCHEMA public
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE email ALTER text_content SET DEFAULT ''
            SQL);
    }
}
