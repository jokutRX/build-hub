<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260812111106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supply_request ADD COLUMN status VARCHAR(50) DEFAULT \'NEW\' NOT NULL');
        $this->addSql('ALTER TABLE supply_request ADD COLUMN trip_id INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__supply_request AS SELECT id, title, site, quantity, unit, priority, delivery_time_start, delivery_time_end, unloading_equipment, calculation_result, created_at FROM supply_request');
        $this->addSql('DROP TABLE supply_request');
        $this->addSql('CREATE TABLE supply_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, site VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, unit VARCHAR(50) NOT NULL, priority VARCHAR(20) NOT NULL, delivery_time_start VARCHAR(10) DEFAULT NULL, delivery_time_end VARCHAR(10) DEFAULT NULL, unloading_equipment VARCHAR(50) DEFAULT NULL, calculation_result CLOB DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO supply_request (id, title, site, quantity, unit, priority, delivery_time_start, delivery_time_end, unloading_equipment, calculation_result, created_at) SELECT id, title, site, quantity, unit, priority, delivery_time_start, delivery_time_end, unloading_equipment, calculation_result, created_at FROM __temp__supply_request');
        $this->addSql('DROP TABLE __temp__supply_request');
        $this->addSql('CREATE INDEX idx_supply_request_created_at ON supply_request (created_at)');
    }
}
