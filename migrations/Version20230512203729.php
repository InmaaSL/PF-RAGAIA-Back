<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230512203729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_profesional_category DROP FOREIGN KEY FK_BD7FFD6AA76ED395');
        $this->addSql('ALTER TABLE user_profesional_category DROP FOREIGN KEY FK_BD7FFD6AFB78D177');
        $this->addSql('DROP TABLE user_profesional_category');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_profesional_category (user_id INT NOT NULL, profesional_category_id INT NOT NULL, INDEX IDX_BD7FFD6AA76ED395 (user_id), INDEX IDX_BD7FFD6AFB78D177 (profesional_category_id), PRIMARY KEY(user_id, profesional_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_profesional_category ADD CONSTRAINT FK_BD7FFD6AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_profesional_category ADD CONSTRAINT FK_BD7FFD6AFB78D177 FOREIGN KEY (profesional_category_id) REFERENCES profesional_category (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
