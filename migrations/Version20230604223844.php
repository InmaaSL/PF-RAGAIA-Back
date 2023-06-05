<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230604223844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO paid_management (age, max_pay, min_pay, incentive) VALUES
            (5, 5, 1, 0.10),
            (7, 6, 2, 0.15),
            (10, 7, 3, 0.20),
            (12, 8, 4, 0.25),
            (14, 9, 5, 0.30),
            (16, 10, 6, 0.35)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM paid_management WHERE age IN (5, 7, 10, 12, 14, 16)");
    }
}
