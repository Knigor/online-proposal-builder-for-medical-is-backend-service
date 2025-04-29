<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429083619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE manager_lk (id SERIAL NOT NULL, user_id_id INT DEFAULT NULL, commercial_offers_id_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, email_client VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_95F6DDB79D86650F ON manager_lk (user_id_id)');
        $this->addSql('CREATE INDEX IDX_95F6DDB7BE852467 ON manager_lk (commercial_offers_id_id)');
        $this->addSql('ALTER TABLE manager_lk ADD CONSTRAINT FK_95F6DDB79D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE manager_lk ADD CONSTRAINT FK_95F6DDB7BE852467 FOREIGN KEY (commercial_offers_id_id) REFERENCES commercial_offers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE manager_lk DROP CONSTRAINT FK_95F6DDB79D86650F');
        $this->addSql('ALTER TABLE manager_lk DROP CONSTRAINT FK_95F6DDB7BE852467');
        $this->addSql('DROP TABLE manager_lk');
    }
}
