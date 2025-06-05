<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250605135332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE manager_lk_id_seq CASCADE');
        $this->addSql('ALTER TABLE manager_lk DROP CONSTRAINT fk_95f6ddb79d86650f');
        $this->addSql('ALTER TABLE manager_lk DROP CONSTRAINT fk_95f6ddb7be852467');
        $this->addSql('DROP TABLE manager_lk');
        $this->addSql('ALTER TABLE commercial_offers_items ADD base_license_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commercial_offers_items ADD additional_module_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commercial_offers_items ADD quantity INT NOT NULL');
        $this->addSql('ALTER TABLE commercial_offers_items ADD price INT NOT NULL');
        $this->addSql('ALTER TABLE commercial_offers_items ADD discount DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE commercial_offers_items ADD CONSTRAINT FK_20DF537DD5B54347 FOREIGN KEY (base_license_id) REFERENCES base_license (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commercial_offers_items ADD CONSTRAINT FK_20DF537DC0C813CC FOREIGN KEY (additional_module_id) REFERENCES additional_module (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_20DF537DD5B54347 ON commercial_offers_items (base_license_id)');
        $this->addSql('CREATE INDEX IDX_20DF537DC0C813CC ON commercial_offers_items (additional_module_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE manager_lk_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE manager_lk (id SERIAL NOT NULL, user_id_id INT DEFAULT NULL, commercial_offers_id_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, email_client VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_95f6ddb7be852467 ON manager_lk (commercial_offers_id_id)');
        $this->addSql('CREATE INDEX idx_95f6ddb79d86650f ON manager_lk (user_id_id)');
        $this->addSql('ALTER TABLE manager_lk ADD CONSTRAINT fk_95f6ddb79d86650f FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE manager_lk ADD CONSTRAINT fk_95f6ddb7be852467 FOREIGN KEY (commercial_offers_id_id) REFERENCES commercial_offers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commercial_offers_items DROP CONSTRAINT FK_20DF537DD5B54347');
        $this->addSql('ALTER TABLE commercial_offers_items DROP CONSTRAINT FK_20DF537DC0C813CC');
        $this->addSql('DROP INDEX IDX_20DF537DD5B54347');
        $this->addSql('DROP INDEX IDX_20DF537DC0C813CC');
        $this->addSql('ALTER TABLE commercial_offers_items DROP base_license_id');
        $this->addSql('ALTER TABLE commercial_offers_items DROP additional_module_id');
        $this->addSql('ALTER TABLE commercial_offers_items DROP quantity');
        $this->addSql('ALTER TABLE commercial_offers_items DROP price');
        $this->addSql('ALTER TABLE commercial_offers_items DROP discount');
    }
}
