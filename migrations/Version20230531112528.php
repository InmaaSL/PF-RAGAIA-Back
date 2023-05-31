<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230531112528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE objective (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type_id INT NOT NULL, month VARCHAR(10) NOT NULL, year VARCHAR(10) NOT NULL, need_detected LONGTEXT NOT NULL, objective LONGTEXT NOT NULL, indicator LONGTEXT NOT NULL, valuation LONGTEXT NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_B996F101A76ED395 (user_id), INDEX IDX_B996F101C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE objective ADD CONSTRAINT FK_B996F101A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE objective ADD CONSTRAINT FK_B996F101C54C8C93 FOREIGN KEY (type_id) REFERENCES objective_type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE objective DROP FOREIGN KEY FK_B996F101A76ED395');
        $this->addSql('ALTER TABLE objective DROP FOREIGN KEY FK_B996F101C54C8C93');
        $this->addSql('DROP TABLE objective');
    }
}
