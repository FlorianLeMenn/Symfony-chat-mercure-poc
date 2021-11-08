<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211108160737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_conversation CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE message CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user ADD status TINYINT(1) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD activation_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_conversation CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE message CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `user` DROP status, DROP created_at, DROP updated_at, DROP activation_token');
    }
}
