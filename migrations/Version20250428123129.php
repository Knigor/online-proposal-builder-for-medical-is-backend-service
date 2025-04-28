<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428123129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commercial_offers_items DROP quantity');
        $this->addSql('ALTER TABLE commercial_offers_items DROP unit_price');
        $this->addSql('ALTER TABLE commercial_offers_items DROP discount_percent');
        $this->addSql('ALTER TABLE price_list ADD quantity INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE price_list DROP quantity');
        $this->addSql('ALTER TABLE commercial_offers_items ADD quantity INT NOT NULL');
        $this->addSql('ALTER TABLE commercial_offers_items ADD unit_price INT NOT NULL');
        $this->addSql('ALTER TABLE commercial_offers_items ADD discount_percent INT NOT NULL');
    }
}
