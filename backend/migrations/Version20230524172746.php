<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230524172746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE deposit (deposit_id INT AUTO_INCREMENT NOT NULL, worth DOUBLE PRECISION NOT NULL, PRIMARY KEY(deposit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (product_id INT AUTO_INCREMENT NOT NULL, deposit_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, sales_price DOUBLE PRECISION NOT NULL, bundable TINYINT(1) NOT NULL, sellable TINYINT(1) NOT NULL, rentable TINYINT(1) NOT NULL, INDEX IDX_D34A04AD9815E4B1 (deposit_id), PRIMARY KEY(product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_deposit (product_deposit_id INT AUTO_INCREMENT NOT NULL, deposit_id INT DEFAULT NULL, INDEX IDX_440286899815E4B1 (deposit_id), PRIMARY KEY(product_deposit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_in_bundle (product_bundle_id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, count INT NOT NULL, INDEX IDX_7C2C2D554584665A (product_id), PRIMARY KEY(product_bundle_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_union (product_union_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(product_union_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_union_product (product_union_id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, INDEX IDX_EED5BD3E4584665A (product_id), PRIMARY KEY(product_union_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD9815E4B1 FOREIGN KEY (deposit_id) REFERENCES deposit (deposit_id)');
        $this->addSql('ALTER TABLE product_deposit ADD CONSTRAINT FK_440286899815E4B1 FOREIGN KEY (deposit_id) REFERENCES deposit (deposit_id)');
        $this->addSql('ALTER TABLE product_in_bundle ADD CONSTRAINT FK_7C2C2D554584665A FOREIGN KEY (product_id) REFERENCES product (product_id)');
        $this->addSql('ALTER TABLE product_union_product ADD CONSTRAINT FK_EED5BD3E4584665A FOREIGN KEY (product_id) REFERENCES product (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD9815E4B1');
        $this->addSql('ALTER TABLE product_deposit DROP FOREIGN KEY FK_440286899815E4B1');
        $this->addSql('ALTER TABLE product_in_bundle DROP FOREIGN KEY FK_7C2C2D554584665A');
        $this->addSql('ALTER TABLE product_union_product DROP FOREIGN KEY FK_EED5BD3E4584665A');
        $this->addSql('DROP TABLE deposit');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_deposit');
        $this->addSql('DROP TABLE product_in_bundle');
        $this->addSql('DROP TABLE product_union');
        $this->addSql('DROP TABLE product_union_product');
    }
}
