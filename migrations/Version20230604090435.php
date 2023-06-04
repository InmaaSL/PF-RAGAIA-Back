<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230604090435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_message ADD post_id INT NOT NULL');
        $this->addSql('ALTER TABLE post_message ADD CONSTRAINT FK_8B55874CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post_message ADD CONSTRAINT FK_8B55874C4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('CREATE INDEX IDX_8B55874C4B89032C ON post_message (post_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_message DROP FOREIGN KEY FK_8B55874CA76ED395');
        $this->addSql('ALTER TABLE post_message DROP FOREIGN KEY FK_8B55874C4B89032C');
        $this->addSql('DROP INDEX IDX_8B55874C4B89032C ON post_message');
        $this->addSql('ALTER TABLE post_message DROP post_id');
    }
}
