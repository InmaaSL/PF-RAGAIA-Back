<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230601132406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE calendar_entry (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, worker_id INT DEFAULT NULL, entry_date DATE NOT NULL, entry_time TIME DEFAULT NULL, all_day TINYINT(1) NOT NULL, event TINYINT(1) NOT NULL, task TINYINT(1) NOT NULL, reminder TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, place VARCHAR(255) DEFAULT NULL, remember TINYINT(1) NOT NULL, INDEX IDX_47759E1EA76ED395 (user_id), INDEX IDX_47759E1E6B20BA36 (worker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE calendar_entry ADD CONSTRAINT FK_47759E1EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE calendar_entry ADD CONSTRAINT FK_47759E1E6B20BA36 FOREIGN KEY (worker_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calendar_entry DROP FOREIGN KEY FK_47759E1EA76ED395');
        $this->addSql('ALTER TABLE calendar_entry DROP FOREIGN KEY FK_47759E1E6B20BA36');
        $this->addSql('DROP TABLE calendar_entry');
    }
}
