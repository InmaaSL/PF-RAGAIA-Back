<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230507172751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO profesional_category (name) VALUES (\'Directora\'), (\'Psicóloga \'),(\'Trabajadora Social\'), 
        (\'Educadora Social \'),(\'Técnico en Integración Social\'), (\'Mediadora \'),(\'Apoyo Doméstico\'), (\'Administradora \')');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM profesional_category');
    }
}
