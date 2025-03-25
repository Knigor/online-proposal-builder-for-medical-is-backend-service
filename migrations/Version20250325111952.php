<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325111952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commercial_offers (id SERIAL NOT NULL, selected_products JSON NOT NULL, status BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN commercial_offers.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE commercial_offers_user (commercial_offers_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(commercial_offers_id, user_id))');
        $this->addSql('CREATE INDEX IDX_2D344D77A1F7C13A ON commercial_offers_user (commercial_offers_id)');
        $this->addSql('CREATE INDEX IDX_2D344D77A76ED395 ON commercial_offers_user (user_id)');
        $this->addSql('ALTER TABLE commercial_offers_user ADD CONSTRAINT FK_2D344D77A1F7C13A FOREIGN KEY (commercial_offers_id) REFERENCES commercial_offers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commercial_offers_user ADD CONSTRAINT FK_2D344D77A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commercial_offers_user DROP CONSTRAINT FK_2D344D77A1F7C13A');
        $this->addSql('ALTER TABLE commercial_offers_user DROP CONSTRAINT FK_2D344D77A76ED395');
        $this->addSql('DROP TABLE commercial_offers');
        $this->addSql('DROP TABLE commercial_offers_user');
    }
}
