<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211012052405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_conversation ADD admin_id INT DEFAULT NULL, CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE group_conversation ADD CONSTRAINT FK_66C86CD0642B8210 FOREIGN KEY (admin_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_66C86CD0642B8210 ON group_conversation (admin_id)');
        $this->addSql('ALTER TABLE message CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_conversation DROP FOREIGN KEY FK_66C86CD0642B8210');
        $this->addSql('DROP INDEX IDX_66C86CD0642B8210 ON group_conversation');
        $this->addSql('ALTER TABLE group_conversation DROP admin_id, CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE message CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
    }
}
