<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617081605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE postal_codes (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(5) NOT NULL, city VARCHAR(100) NOT NULL, zone_id INT NOT NULL, UNIQUE INDEX UNIQ_A28283E177153098 (code), INDEX IDX_A28283E19F2C3FAB (zone_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE shipping_zones (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, zone_surcharge NUMERIC(8, 2) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_6B556CD25E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE weight_tariffs (id INT AUTO_INCREMENT NOT NULL, min_weight NUMERIC(8, 3) NOT NULL, max_weight NUMERIC(8, 3) DEFAULT NULL, base_price NUMERIC(8, 2) NOT NULL, weight_unit_price NUMERIC(8, 4) NOT NULL, zone_id INT NOT NULL, INDEX IDX_CAEA93CE9F2C3FAB (zone_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE postal_codes ADD CONSTRAINT FK_A28283E19F2C3FAB FOREIGN KEY (zone_id) REFERENCES shipping_zones (id)');
        $this->addSql('ALTER TABLE weight_tariffs ADD CONSTRAINT FK_CAEA93CE9F2C3FAB FOREIGN KEY (zone_id) REFERENCES shipping_zones (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE postal_codes DROP FOREIGN KEY FK_A28283E19F2C3FAB');
        $this->addSql('ALTER TABLE weight_tariffs DROP FOREIGN KEY FK_CAEA93CE9F2C3FAB');
        $this->addSql('DROP TABLE postal_codes');
        $this->addSql('DROP TABLE shipping_zones');
        $this->addSql('DROP TABLE weight_tariffs');
    }
}
