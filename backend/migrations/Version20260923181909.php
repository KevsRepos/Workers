<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260923181909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD default_shipping_address_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\' AFTER company_name, ADD default_billing_address_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\' AFTER default_shipping_address_id');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09E4901476 FOREIGN KEY (default_shipping_address_id) REFERENCES customer_address (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E091995CE08 FOREIGN KEY (default_billing_address_id) REFERENCES customer_address (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81398E09E4901476 ON customer (default_shipping_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81398E091995CE08 ON customer (default_billing_address_id)');
        $this->addSql('ALTER TABLE customer_address DROP `default`, DROP address_type');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_address ADD `default` TINYINT(1) NOT NULL, ADD address_type INT NOT NULL');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09E4901476');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E091995CE08');
        $this->addSql('DROP INDEX UNIQ_81398E09E4901476 ON customer');
        $this->addSql('DROP INDEX UNIQ_81398E091995CE08 ON customer');
        $this->addSql('ALTER TABLE customer DROP default_shipping_address_id, DROP default_billing_address_id');
    }
}
