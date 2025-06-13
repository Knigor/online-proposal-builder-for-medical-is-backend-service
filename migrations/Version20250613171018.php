<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613171018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer (id SERIAL NOT NULL, name_customer VARCHAR(255) NOT NULL, contact_person VARCHAR(255) NOT NULL, phone VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE commercial_offers ADD customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commercial_offers ADD CONSTRAINT FK_96006A7B9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_96006A7B9395C3F3 ON commercial_offers (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commercial_offers DROP CONSTRAINT FK_96006A7B9395C3F3');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP INDEX IDX_96006A7B9395C3F3');
        $this->addSql('ALTER TABLE commercial_offers DROP customer_id');
    }
}
