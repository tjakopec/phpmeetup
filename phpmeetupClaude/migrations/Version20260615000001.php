<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260615000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create shipping zones, postal codes, and weight tariffs tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE shipping_zones (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR(50) NOT NULL,
            zone_surcharge NUMERIC(8, 2) NOT NULL,
            description CLOB DEFAULT NULL,
            CONSTRAINT UNIQ_shipping_zones_name UNIQUE (name)
        )');

        $this->addSql('CREATE TABLE postal_codes (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            zone_id INTEGER NOT NULL,
            code VARCHAR(5) NOT NULL,
            city VARCHAR(100) NOT NULL,
            CONSTRAINT UNIQ_postal_codes_code UNIQUE (code),
            CONSTRAINT FK_postal_codes_zone FOREIGN KEY (zone_id) REFERENCES shipping_zones (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');

        $this->addSql('CREATE INDEX IDX_postal_codes_zone ON postal_codes (zone_id)');

        $this->addSql('CREATE TABLE weight_tariffs (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            zone_id INTEGER NOT NULL,
            min_weight NUMERIC(8, 3) NOT NULL,
            max_weight NUMERIC(8, 3) DEFAULT NULL,
            base_price NUMERIC(8, 2) NOT NULL,
            weight_unit_price NUMERIC(8, 4) NOT NULL,
            CONSTRAINT FK_weight_tariffs_zone FOREIGN KEY (zone_id) REFERENCES shipping_zones (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');

        $this->addSql('CREATE INDEX IDX_weight_tariffs_zone ON weight_tariffs (zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE weight_tariffs');
        $this->addSql('DROP TABLE postal_codes');
        $this->addSql('DROP TABLE shipping_zones');
    }
}
