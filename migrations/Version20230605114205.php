<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230605114205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO paid_management (age, age_range, max_pay, min_pay, incentive) VALUES
            (5, '[0, 5]', 5, 1, 0.10),
            (6, '[6, 9]', 6, 2, 0.15),
            (10, '[10, 11]', 7, 3, 0.20),
            (12, '[12, 13]', 8, 4, 0.25),
            (14, '[14, 15]', 9, 5, 0.30),
            (16, '[16, 20]', 10, 6, 0.35)"
            );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM paid_management WHERE age IN (5, 6, 10, 12, 14, 16)");

    }
}
