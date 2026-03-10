<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309234619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE delivery_note_product (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', delivery_note_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', quantity INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CD6F39332CF3B78B (delivery_note_id), INDEX IDX_CD6F39334584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE delivery_note_product ADD CONSTRAINT FK_CD6F39332CF3B78B FOREIGN KEY (delivery_note_id) REFERENCES delivery_note (id)');
        $this->addSql('ALTER TABLE delivery_note_product ADD CONSTRAINT FK_CD6F39334584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_note_product DROP FOREIGN KEY FK_CD6F39332CF3B78B');
        $this->addSql('ALTER TABLE delivery_note_product DROP FOREIGN KEY FK_CD6F39334584665A');
        $this->addSql('DROP TABLE delivery_note_product');
    }
}
