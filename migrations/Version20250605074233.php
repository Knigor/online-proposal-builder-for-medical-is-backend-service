<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250605074233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE price_list_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE document_id_seq CASCADE');
        $this->addSql('CREATE TABLE additional_module (id SERIAL NOT NULL, name_module VARCHAR(255) NOT NULL, description_module TEXT NOT NULL, offer_price DOUBLE PRECISION NOT NULL, purchase_price DOUBLE PRECISION NOT NULL, max_discount_percent DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE base_license (id SERIAL NOT NULL, name_license VARCHAR(255) NOT NULL, description_license TEXT NOT NULL, offer_price_license DOUBLE PRECISION NOT NULL, purchase_price_license DOUBLE PRECISION NOT NULL, max_discount DOUBLE PRECISION NOT NULL, type_license VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE discount_level (id SERIAL NOT NULL, product_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, min_licenses INT DEFAULT NULL, max_licenses INT DEFAULT NULL, min_amount INT DEFAULT NULL, max_amount INT DEFAULT NULL, discount_percent DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B1C014844584665A ON discount_level (product_id)');
        $this->addSql('CREATE TABLE license_composition (id SERIAL NOT NULL, required BOOLEAN NOT NULL, compatible BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE discount_level ADD CONSTRAINT FK_B1C014844584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE price_list DROP CONSTRAINT fk_399a0aa24584665a');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT fk_d8698a76efc266af');
        $this->addSql('DROP TABLE price_list');
        $this->addSql('DROP TABLE document');
        $this->addSql('ALTER TABLE commercial_offers_items DROP CONSTRAINT fk_20df537dde18e50b');
        $this->addSql('DROP INDEX idx_20df537dde18e50b');
        $this->addSql('ALTER TABLE commercial_offers_items DROP product_id_id');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT fk_d34a04ad9d86650f');
        $this->addSql('DROP INDEX idx_d34a04ad9d86650f');
        $this->addSql('ALTER TABLE product DROP user_id_id');
        $this->addSql('ALTER TABLE product DROP is_active');
        $this->addSql('ALTER TABLE product DROP type_product');
        $this->addSql('ALTER TABLE product ALTER discription_product TYPE TEXT');
        $this->addSql('ALTER TABLE product ALTER discription_product TYPE TEXT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE price_list_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE document_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE price_list (id SERIAL NOT NULL, product_id INT NOT NULL, price INT NOT NULL, discount_percent INT NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_399a0aa24584665a ON price_list (product_id)');
        $this->addSql('CREATE TABLE document (id SERIAL NOT NULL, commercial_offer_id INT DEFAULT NULL, path_to_file VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_d8698a76efc266af ON document (commercial_offer_id)');
        $this->addSql('ALTER TABLE price_list ADD CONSTRAINT fk_399a0aa24584665a FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT fk_d8698a76efc266af FOREIGN KEY (commercial_offer_id) REFERENCES commercial_offers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE discount_level DROP CONSTRAINT FK_B1C014844584665A');
        $this->addSql('DROP TABLE additional_module');
        $this->addSql('DROP TABLE base_license');
        $this->addSql('DROP TABLE discount_level');
        $this->addSql('DROP TABLE license_composition');
        $this->addSql('ALTER TABLE product ADD user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD is_active BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD type_product VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE product ALTER discription_product TYPE VARCHAR(1000)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT fk_d34a04ad9d86650f FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d34a04ad9d86650f ON product (user_id_id)');
        $this->addSql('ALTER TABLE commercial_offers_items ADD product_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE commercial_offers_items ADD CONSTRAINT fk_20df537dde18e50b FOREIGN KEY (product_id_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_20df537dde18e50b ON commercial_offers_items (product_id_id)');
    }
}
