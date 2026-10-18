<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261018163304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_preset (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_preset_item (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', order_preset_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_85A115293CEFF597 (order_preset_id), INDEX IDX_85A115294584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_preset_item ADD CONSTRAINT FK_85A115293CEFF597 FOREIGN KEY (order_preset_id) REFERENCES order_preset (id)');
        $this->addSql('ALTER TABLE order_preset_item ADD CONSTRAINT FK_85A115294584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_template_product DROP FOREIGN KEY FK_E7E1B68A9F591A7');
        $this->addSql('ALTER TABLE product_template_product DROP FOREIGN KEY FK_E7E1B684584665A');
        $this->addSql('DROP TABLE product_template');
        $this->addSql('DROP TABLE product_template_product');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_template (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE product_template_product (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E7E1B68A9F591A7 (product_template_id), INDEX IDX_E7E1B684584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product_template_product ADD CONSTRAINT FK_E7E1B68A9F591A7 FOREIGN KEY (product_template_id) REFERENCES product_template (id)');
        $this->addSql('ALTER TABLE product_template_product ADD CONSTRAINT FK_E7E1B684584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE order_preset_item DROP FOREIGN KEY FK_85A115293CEFF597');
        $this->addSql('ALTER TABLE order_preset_item DROP FOREIGN KEY FK_85A115294584665A');
        $this->addSql('DROP TABLE order_preset');
        $this->addSql('DROP TABLE order_preset_item');
    }
}
