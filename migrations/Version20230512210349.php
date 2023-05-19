<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230512210349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_professional_category_centre ADD professional_category_id INT DEFAULT NULL, ADD centre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_professional_category_centre ADD CONSTRAINT FK_2640C05DA0CCFBB2 FOREIGN KEY (professional_category_id) REFERENCES professional_category (id)');
        $this->addSql('ALTER TABLE user_professional_category_centre ADD CONSTRAINT FK_2640C05D463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)');
        $this->addSql('CREATE INDEX IDX_2640C05DA0CCFBB2 ON user_professional_category_centre (professional_category_id)');
        $this->addSql('CREATE INDEX IDX_2640C05D463CD7C3 ON user_professional_category_centre (centre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_professional_category_centre DROP FOREIGN KEY FK_2640C05DA0CCFBB2');
        $this->addSql('ALTER TABLE user_professional_category_centre DROP FOREIGN KEY FK_2640C05D463CD7C3');
        $this->addSql('DROP INDEX IDX_2640C05DA0CCFBB2 ON user_professional_category_centre');
        $this->addSql('DROP INDEX IDX_2640C05D463CD7C3 ON user_professional_category_centre');
        $this->addSql('ALTER TABLE user_professional_category_centre DROP professional_category_id, DROP centre_id');
    }
}
