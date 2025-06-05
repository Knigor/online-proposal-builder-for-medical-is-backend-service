<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250605074834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE additional_module ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE additional_module ADD CONSTRAINT FK_5D86D4694584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5D86D4694584665A ON additional_module (product_id)');
        $this->addSql('ALTER TABLE base_license ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE base_license ADD CONSTRAINT FK_636E4D7D4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_636E4D7D4584665A ON base_license (product_id)');
        $this->addSql('ALTER TABLE license_composition ADD base_license_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE license_composition ADD additional_module_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE license_composition ADD CONSTRAINT FK_2E6A5A01D5B54347 FOREIGN KEY (base_license_id) REFERENCES base_license (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE license_composition ADD CONSTRAINT FK_2E6A5A01C0C813CC FOREIGN KEY (additional_module_id) REFERENCES additional_module (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2E6A5A01D5B54347 ON license_composition (base_license_id)');
        $this->addSql('CREATE INDEX IDX_2E6A5A01C0C813CC ON license_composition (additional_module_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE license_composition DROP CONSTRAINT FK_2E6A5A01D5B54347');
        $this->addSql('ALTER TABLE license_composition DROP CONSTRAINT FK_2E6A5A01C0C813CC');
        $this->addSql('DROP INDEX IDX_2E6A5A01D5B54347');
        $this->addSql('DROP INDEX IDX_2E6A5A01C0C813CC');
        $this->addSql('ALTER TABLE license_composition DROP base_license_id');
        $this->addSql('ALTER TABLE license_composition DROP additional_module_id');
        $this->addSql('ALTER TABLE base_license DROP CONSTRAINT FK_636E4D7D4584665A');
        $this->addSql('DROP INDEX IDX_636E4D7D4584665A');
        $this->addSql('ALTER TABLE base_license DROP product_id');
        $this->addSql('ALTER TABLE additional_module DROP CONSTRAINT FK_5D86D4694584665A');
        $this->addSql('DROP INDEX IDX_5D86D4694584665A');
        $this->addSql('ALTER TABLE additional_module DROP product_id');
    }
}
