<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260807170402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supply_request ADD COLUMN delivery_time_start VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE supply_request ADD COLUMN delivery_time_end VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE supply_request ADD COLUMN unloading_equipment VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__supply_request AS SELECT id, title, site, quantity, unit, priority, created_at FROM supply_request');
        $this->addSql('DROP TABLE supply_request');
        $this->addSql('CREATE TABLE supply_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, site VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, unit VARCHAR(50) NOT NULL, priority VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO supply_request (id, title, site, quantity, unit, priority, created_at) SELECT id, title, site, quantity, unit, priority, created_at FROM __temp__supply_request');
        $this->addSql('DROP TABLE __temp__supply_request');
    }
}
