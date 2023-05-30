<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230527173932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE health_records (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, worker_id INT NOT NULL, type_consultation VARCHAR(100) DEFAULT NULL, date DATETIME NOT NULL, what_happens LONGTEXT NOT NULL, diagnostic LONGTEXT NOT NULL, treatment LONGTEXT NOT NULL, revision LONGTEXT NOT NULL, INDEX IDX_134C501A76ED395 (user_id), INDEX IDX_134C5016B20BA36 (worker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE health_records ADD CONSTRAINT FK_134C501A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE health_records ADD CONSTRAINT FK_134C5016B20BA36 FOREIGN KEY (worker_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE health_records DROP FOREIGN KEY FK_134C501A76ED395');
        $this->addSql('ALTER TABLE health_records DROP FOREIGN KEY FK_134C5016B20BA36');
        $this->addSql('DROP TABLE health_records');
    }
}
