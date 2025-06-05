<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520132643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commercial_offers_items DROP CONSTRAINT FK_20DF537DDE18E50B');
        $this->addSql('ALTER TABLE commercial_offers_items ADD CONSTRAINT FK_20DF537DDE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commercial_offers_items DROP CONSTRAINT fk_20df537dde18e50b');
        $this->addSql('ALTER TABLE commercial_offers_items ADD CONSTRAINT fk_20df537dde18e50b FOREIGN KEY (product_id_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
