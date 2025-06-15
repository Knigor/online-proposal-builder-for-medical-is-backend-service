<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615080212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commercial_offers_items DROP CONSTRAINT fk_20df537dc0c813cc');
        $this->addSql('DROP INDEX idx_20df537dc0c813cc');
        $this->addSql('ALTER TABLE commercial_offers_items DROP additional_module_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commercial_offers_items ADD additional_module_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commercial_offers_items ADD CONSTRAINT fk_20df537dc0c813cc FOREIGN KEY (additional_module_id) REFERENCES additional_module (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_20df537dc0c813cc ON commercial_offers_items (additional_module_id)');
    }
}
