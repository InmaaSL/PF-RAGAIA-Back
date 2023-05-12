<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230512203201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_centre DROP FOREIGN KEY FK_A3F2F148463CD7C3');
        $this->addSql('ALTER TABLE user_centre DROP FOREIGN KEY FK_A3F2F148A76ED395');
        $this->addSql('DROP TABLE user_centre');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_centre (user_id INT NOT NULL, centre_id INT NOT NULL, INDEX IDX_A3F2F148A76ED395 (user_id), INDEX IDX_A3F2F148463CD7C3 (centre_id), PRIMARY KEY(user_id, centre_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_centre ADD CONSTRAINT FK_A3F2F148463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_centre ADD CONSTRAINT FK_A3F2F148A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
