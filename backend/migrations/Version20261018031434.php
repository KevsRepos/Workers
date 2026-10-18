<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261018031434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_template (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_template_product (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E7E1B68A9F591A7 (product_template_id), INDEX IDX_E7E1B684584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_template_product ADD CONSTRAINT FK_E7E1B68A9F591A7 FOREIGN KEY (product_template_id) REFERENCES product_template (id)');
        $this->addSql('ALTER TABLE product_template_product ADD CONSTRAINT FK_E7E1B684584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_template_product DROP FOREIGN KEY FK_E7E1B68A9F591A7');
        $this->addSql('ALTER TABLE product_template_product DROP FOREIGN KEY FK_E7E1B684584665A');
        $this->addSql('DROP TABLE product_template');
        $this->addSql('DROP TABLE product_template_product');
    }
}
