<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260923234932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_address CHANGE street street VARCHAR(255) NOT NULL, CHANGE house_number house_number VARCHAR(50) NOT NULL, CHANGE postal_code postal_code VARCHAR(20) NOT NULL, CHANGE city city VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE delivery_note ADD shipping_address_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\' AFTER delivery, ADD billing_address_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\' AFTER shipping_address_id');
        $this->addSql('ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328E4D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES customer_address (id)');
        $this->addSql('ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328E79D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES customer_address (id)');
        $this->addSql('CREATE INDEX IDX_1E21328E4D4CFF2B ON delivery_note (shipping_address_id)');
        $this->addSql('CREATE INDEX IDX_1E21328E79D0C0E4 ON delivery_note (billing_address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_address CHANGE street street VARCHAR(255) DEFAULT NULL, CHANGE house_number house_number VARCHAR(50) DEFAULT NULL, CHANGE postal_code postal_code VARCHAR(20) DEFAULT NULL, CHANGE city city VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery_note DROP FOREIGN KEY FK_1E21328E4D4CFF2B');
        $this->addSql('ALTER TABLE delivery_note DROP FOREIGN KEY FK_1E21328E79D0C0E4');
        $this->addSql('DROP INDEX IDX_1E21328E4D4CFF2B ON delivery_note');
        $this->addSql('DROP INDEX IDX_1E21328E79D0C0E4 ON delivery_note');
        $this->addSql('ALTER TABLE delivery_note DROP shipping_address_id, DROP billing_address_id');
    }
}
