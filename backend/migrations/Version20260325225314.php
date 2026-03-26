<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325225314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE return_note_entry (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', returned_total INT DEFAULT NULL, returned_total_bottles INT DEFAULT NULL, returned_full INT DEFAULT NULL, returned_full_bottles INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE delivery_note_product ADD return_note_entry_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\' AFTER product_id, DROP returned_total, DROP returned_total_bottles, DROP returned_full, DROP returned_full_bottles');
        $this->addSql('ALTER TABLE delivery_note_product ADD CONSTRAINT FK_CD6F39332E08A7D7 FOREIGN KEY (return_note_entry_id) REFERENCES return_note_entry (id)');
        $this->addSql('CREATE INDEX IDX_CD6F39332E08A7D7 ON delivery_note_product (return_note_entry_id)');
        $this->addSql('ALTER TABLE product CHANGE sales_price sales_price INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_note_product DROP FOREIGN KEY FK_CD6F39332E08A7D7');
        $this->addSql('DROP TABLE return_note_entry');
        $this->addSql('ALTER TABLE product CHANGE sales_price sales_price INT NOT NULL');
        $this->addSql('DROP INDEX IDX_CD6F39332E08A7D7 ON delivery_note_product');
        $this->addSql('ALTER TABLE delivery_note_product ADD returned_total INT DEFAULT NULL, ADD returned_total_bottles INT DEFAULT NULL, ADD returned_full INT DEFAULT NULL, ADD returned_full_bottles INT DEFAULT NULL, DROP return_note_entry_id');
    }
}
