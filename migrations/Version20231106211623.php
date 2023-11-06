<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231106211623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE campaign ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE child ADD campaign_id INT NOT NULL');
        $this->addSql('ALTER TABLE child ADD CONSTRAINT FK_22B35429F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
        $this->addSql('CREATE INDEX IDX_22B35429F639F774 ON child (campaign_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE campaign DROP slug');
        $this->addSql('ALTER TABLE child DROP FOREIGN KEY FK_22B35429F639F774');
        $this->addSql('DROP INDEX IDX_22B35429F639F774 ON child');
        $this->addSql('ALTER TABLE child DROP campaign_id');
    }
}
