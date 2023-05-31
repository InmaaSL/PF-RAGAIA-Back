<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230530153642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO objective_type (name) VALUES (\'Objetivos Educacion\'), (\'Objetivos Sanidad\'), (\'Objetivos Emocional\'),
        (\'Objetivos Familiar\'), (\'Objetivos Resiliencia\'), (\'Objetivos Residencia\')');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM objective_type');

    }
}
