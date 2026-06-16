<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260601100717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_office (id INT AUTO_INCREMENT NOT NULL, postal_code VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, currency VARCHAR(3) NOT NULL, shipping_zone_id INT DEFAULT NULL, INDEX IDX_44738F677964396F (shipping_zone_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE service_type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(100) NOT NULL, weight_surcharge DOUBLE PRECISION NOT NULL, dimensional_surcharge DOUBLE PRECISION NOT NULL, priority_multiplier DOUBLE PRECISION NOT NULL, volume_divisor INT NOT NULL, reduces_estimated_delivery_days INT NOT NULL, max_weight DOUBLE PRECISION NOT NULL, max_dimension DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_429DE3C577153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE shipping_zone (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, base_delivery_days INT NOT NULL, zone_surcharge DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tariff (id INT AUTO_INCREMENT NOT NULL, min_weight DOUBLE PRECISION NOT NULL, max_weight DOUBLE PRECISION NOT NULL, base_price DOUBLE PRECISION NOT NULL, service_type_id INT NOT NULL, INDEX IDX_9465207DAC8DE0F (service_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE post_office ADD CONSTRAINT FK_44738F677964396F FOREIGN KEY (shipping_zone_id) REFERENCES shipping_zone (id)');
        $this->addSql('ALTER TABLE tariff ADD CONSTRAINT FK_9465207DAC8DE0F FOREIGN KEY (service_type_id) REFERENCES service_type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_office DROP FOREIGN KEY FK_44738F677964396F');
        $this->addSql('ALTER TABLE tariff DROP FOREIGN KEY FK_9465207DAC8DE0F');
        $this->addSql('DROP TABLE post_office');
        $this->addSql('DROP TABLE service_type');
        $this->addSql('DROP TABLE shipping_zone');
        $this->addSql('DROP TABLE tariff');
        $this->addSql('DROP TABLE `user`');
    }
}
