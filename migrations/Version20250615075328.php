<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615075328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commercial_offers_item_module (id SERIAL NOT NULL, item_id INT DEFAULT NULL, additional_module_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8A4C31EB126F525E ON commercial_offers_item_module (item_id)');
        $this->addSql('CREATE INDEX IDX_8A4C31EBC0C813CC ON commercial_offers_item_module (additional_module_id)');
        $this->addSql('ALTER TABLE commercial_offers_item_module ADD CONSTRAINT FK_8A4C31EB126F525E FOREIGN KEY (item_id) REFERENCES commercial_offers_items (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commercial_offers_item_module ADD CONSTRAINT FK_8A4C31EBC0C813CC FOREIGN KEY (additional_module_id) REFERENCES additional_module (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commercial_offers_item_module DROP CONSTRAINT FK_8A4C31EB126F525E');
        $this->addSql('ALTER TABLE commercial_offers_item_module DROP CONSTRAINT FK_8A4C31EBC0C813CC');
        $this->addSql('DROP TABLE commercial_offers_item_module');
    }
}
