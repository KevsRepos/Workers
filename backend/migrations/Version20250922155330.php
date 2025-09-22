<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922155330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (account_id INT AUTO_INCREMENT NOT NULL, email_address VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, surname VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_7D3656A4B08E074E (email_address), PRIMARY KEY(account_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deposit (deposit_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', single_amount INT NOT NULL, crate_amount INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(deposit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', deposit_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, sales_price INT NOT NULL, sellable TINYINT(1) NOT NULL, rentable TINYINT(1) NOT NULL, quantity_in_crate INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D34A04AD9815E4B1 (deposit_id), PRIMARY KEY(product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_union (product_union_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(product_union_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_union_product (product_union_product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_union_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', product_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_EED5BD3EE7CB3FAE (product_union_id), INDEX IDX_EED5BD3E4584665A (product_id), PRIMARY KEY(product_union_product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD9815E4B1 FOREIGN KEY (deposit_id) REFERENCES deposit (deposit_id)');
        $this->addSql('ALTER TABLE product_union_product ADD CONSTRAINT FK_EED5BD3EE7CB3FAE FOREIGN KEY (product_union_id) REFERENCES product_union (product_union_id)');
        $this->addSql('ALTER TABLE product_union_product ADD CONSTRAINT FK_EED5BD3E4584665A FOREIGN KEY (product_id) REFERENCES product (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD9815E4B1');
        $this->addSql('ALTER TABLE product_union_product DROP FOREIGN KEY FK_EED5BD3EE7CB3FAE');
        $this->addSql('ALTER TABLE product_union_product DROP FOREIGN KEY FK_EED5BD3E4584665A');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE deposit');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_union');
        $this->addSql('DROP TABLE product_union_product');
    }
}
