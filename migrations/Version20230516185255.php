<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230516185255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_data ADD custody_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_data ADD CONSTRAINT FK_D772BFAA43B1ACE2 FOREIGN KEY (custody_id) REFERENCES custody (id)');
        $this->addSql('CREATE INDEX IDX_D772BFAA43B1ACE2 ON user_data (custody_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_data DROP FOREIGN KEY FK_D772BFAA43B1ACE2');
        $this->addSql('DROP INDEX IDX_D772BFAA43B1ACE2 ON user_data');
        $this->addSql('ALTER TABLE user_data DROP custody_id');
    }
}
