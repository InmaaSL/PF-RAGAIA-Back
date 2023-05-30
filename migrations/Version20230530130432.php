<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230530130432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE education_record ADD worker_id INT NOT NULL');
        $this->addSql('ALTER TABLE education_record ADD CONSTRAINT FK_D33A25C06B20BA36 FOREIGN KEY (worker_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D33A25C06B20BA36 ON education_record (worker_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE education_record DROP FOREIGN KEY FK_D33A25C06B20BA36');
        $this->addSql('DROP INDEX IDX_D33A25C06B20BA36 ON education_record');
        $this->addSql('ALTER TABLE education_record DROP worker_id');
    }
}
