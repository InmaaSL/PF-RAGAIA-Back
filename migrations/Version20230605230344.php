<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230605230344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pay_register (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, week_start DATETIME NOT NULL, week_end DATETIME NOT NULL, base_pay DOUBLE PRECISION DEFAULT NULL, max_pay DOUBLE PRECISION DEFAULT NULL, percent_measure INT DEFAULT NULL, base_pay_rest DOUBLE PRECISION DEFAULT NULL, max_incentive DOUBLE PRECISION DEFAULT NULL, incentive DOUBLE PRECISION DEFAULT NULL, max_study DOUBLE PRECISION DEFAULT NULL, study DOUBLE PRECISION DEFAULT NULL, max_bedroom DOUBLE PRECISION DEFAULT NULL, bedroom DOUBLE PRECISION DEFAULT NULL, total_incentive DOUBLE PRECISION DEFAULT NULL, negative_pay VARCHAR(30) DEFAULT NULL, total_pay DOUBLE PRECISION DEFAULT NULL, INDEX IDX_53A9B1EBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pay_register ADD CONSTRAINT FK_53A9B1EBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pay_register DROP FOREIGN KEY FK_53A9B1EBA76ED395');
        $this->addSql('DROP TABLE pay_register');
    }
}
