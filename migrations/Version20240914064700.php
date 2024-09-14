<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240914064700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Comment and CommentLikeEvent entities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE comment (id INT NOT NULL, commenter_id INT NOT NULL, question_id INT NOT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9474526CB4D5A9E2 ON comment (commenter_id)');
        $this->addSql('CREATE INDEX IDX_9474526C1E27F6BF ON comment (question_id)');
        $this->addSql('COMMENT ON COLUMN comment.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN comment.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE comment_like_event (id UUID NOT NULL, liker_id INT NOT NULL, comment_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E2BA960979F103A ON comment_like_event (liker_id)');
        $this->addSql('CREATE INDEX IDX_E2BA960F8697D13 ON comment_like_event (comment_id)');
        $this->addSql('COMMENT ON COLUMN comment_like_event.id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CB4D5A9E2 FOREIGN KEY (commenter_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment_like_event ADD CONSTRAINT FK_E2BA960979F103A FOREIGN KEY (liker_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment_like_event ADD CONSTRAINT FK_E2BA960F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE comment_id_seq CASCADE');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526CB4D5A9E2');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526C1E27F6BF');
        $this->addSql('ALTER TABLE comment_like_event DROP CONSTRAINT FK_E2BA960979F103A');
        $this->addSql('ALTER TABLE comment_like_event DROP CONSTRAINT FK_E2BA960F8697D13');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE comment_like_event');
    }
}
