<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250425135739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commercial_offers_items (id SERIAL NOT NULL, commercial_offer_id_id INT NOT NULL, product_id_id INT NOT NULL, quantity INT NOT NULL, unit_price INT NOT NULL, discount_percent INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_20DF537DF2A8B03F ON commercial_offers_items (commercial_offer_id_id)');
        $this->addSql('CREATE INDEX IDX_20DF537DDE18E50B ON commercial_offers_items (product_id_id)');
        $this->addSql('ALTER TABLE commercial_offers_items ADD CONSTRAINT FK_20DF537DF2A8B03F FOREIGN KEY (commercial_offer_id_id) REFERENCES commercial_offers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commercial_offers_items ADD CONSTRAINT FK_20DF537DDE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commercial_offers DROP selected_products');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commercial_offers_items DROP CONSTRAINT FK_20DF537DF2A8B03F');
        $this->addSql('ALTER TABLE commercial_offers_items DROP CONSTRAINT FK_20DF537DDE18E50B');
        $this->addSql('DROP TABLE commercial_offers_items');
        $this->addSql('ALTER TABLE commercial_offers ADD selected_products JSON NOT NULL');
    }
}
