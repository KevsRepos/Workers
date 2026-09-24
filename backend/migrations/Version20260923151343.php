<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260923151343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_address CHANGE street street VARCHAR(255) DEFAULT NULL, CHANGE house_number house_number VARCHAR(50) DEFAULT NULL, CHANGE postal_code postal_code VARCHAR(20) DEFAULT NULL, CHANGE city city VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE monthly_time_sheet_entry CHANGE type type INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_address CHANGE street street VARCHAR(255) NOT NULL, CHANGE house_number house_number VARCHAR(50) NOT NULL, CHANGE postal_code postal_code VARCHAR(20) NOT NULL, CHANGE city city VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE monthly_time_sheet_entry CHANGE type type INT DEFAULT 1 NOT NULL');
    }
}
