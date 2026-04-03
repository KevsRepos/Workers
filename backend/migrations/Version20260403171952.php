<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403171952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE monthly_time_sheet (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', account_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', month INT NOT NULL, year INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B53101B9B6B5FBA (account_id), UNIQUE INDEX unique_account_month_year (account_id, month, year), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE monthly_time_sheet_entry (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', monthly_time_sheet_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', day INT NOT NULL, start TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', break_duration INT NOT NULL, end TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7D055B9BCE929BB5 (monthly_time_sheet_id), UNIQUE INDEX unique_entry_per_day (monthly_time_sheet_id, day), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE monthly_time_sheet ADD CONSTRAINT FK_B53101B9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE monthly_time_sheet_entry ADD CONSTRAINT FK_7D055B9BCE929BB5 FOREIGN KEY (monthly_time_sheet_id) REFERENCES monthly_time_sheet (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monthly_time_sheet DROP FOREIGN KEY FK_B53101B9B6B5FBA');
        $this->addSql('ALTER TABLE monthly_time_sheet_entry DROP FOREIGN KEY FK_7D055B9BCE929BB5');
        $this->addSql('DROP TABLE monthly_time_sheet');
        $this->addSql('DROP TABLE monthly_time_sheet_entry');
    }
}
