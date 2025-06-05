<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250605075854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commercial_offers_items ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commercial_offers_items ADD CONSTRAINT FK_20DF537D4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_20DF537D4584665A ON commercial_offers_items (product_id)');
        $this->addSql('ALTER TABLE product ADD user_product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADE2E3A0B6 FOREIGN KEY (user_product_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D34A04ADE2E3A0B6 ON product (user_product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commercial_offers_items DROP CONSTRAINT FK_20DF537D4584665A');
        $this->addSql('DROP INDEX IDX_20DF537D4584665A');
        $this->addSql('ALTER TABLE commercial_offers_items DROP product_id');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04ADE2E3A0B6');
        $this->addSql('DROP INDEX IDX_D34A04ADE2E3A0B6');
        $this->addSql('ALTER TABLE product DROP user_product_id');
    }
}
