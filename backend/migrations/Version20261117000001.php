<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261117000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Baseline schema for all application entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE account (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, email_address VARCHAR(180) NOT NULL, first_name VARCHAR(100) NOT NULL, surname VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, active TINYINT DEFAULT 1 NOT NULL, last_login_at DATETIME DEFAULT NULL, password_changed_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_7D3656A4B08E074E (email_address), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE customer (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, company TINYINT NOT NULL, first_name VARCHAR(100) DEFAULT NULL, surname VARCHAR(100) DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, anonymized_at DATETIME DEFAULT NULL, default_shipping_address_id BINARY(16) DEFAULT NULL, default_billing_address_id BINARY(16) DEFAULT NULL, INDEX idx_customer_surname (surname), INDEX idx_customer_company_name (company_name), INDEX IDX_81398E09E4901476 (default_shipping_address_id), INDEX IDX_81398E091995CE08 (default_billing_address_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE customer_address (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, street VARCHAR(255) NOT NULL, house_number VARCHAR(20) NOT NULL, postal_code VARCHAR(20) NOT NULL, city VARCHAR(100) NOT NULL, country VARCHAR(100) DEFAULT NULL, note VARCHAR(255) DEFAULT NULL, customer_id BINARY(16) NOT NULL, INDEX IDX_1193CB3F9395C3F3 (customer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE delivery_note (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, number VARCHAR(32) NOT NULL, delivery_date DATE NOT NULL, pickup_date DATE DEFAULT NULL, delivery TINYINT NOT NULL, status INT NOT NULL, short_description LONGTEXT DEFAULT NULL, private_description LONGTEXT DEFAULT NULL, assignment VARCHAR(255) DEFAULT NULL, adult_guests INT DEFAULT NULL, child_guests INT DEFAULT NULL, call_before TINYINT NOT NULL, customer_id BINARY(16) NOT NULL, shipping_address_id BINARY(16) DEFAULT NULL, billing_address_id BINARY(16) DEFAULT NULL, created_by_id BINARY(16) DEFAULT NULL, UNIQUE INDEX UNIQ_1E21328E96901F54 (number), INDEX idx_delivery_note_status_date (status, delivery_date), INDEX idx_delivery_note_delivery_date (delivery_date), INDEX IDX_1E21328E9395C3F3 (customer_id), INDEX IDX_1E21328E4D4CFF2B (shipping_address_id), INDEX IDX_1E21328E79D0C0E4 (billing_address_id), INDEX IDX_1E21328EB03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE delivery_note_product (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, product_name VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION DEFAULT NULL, position INT DEFAULT 0 NOT NULL, delivery_note_id BINARY(16) NOT NULL, product_id BINARY(16) NOT NULL, return_note_entry_id BINARY(16) DEFAULT NULL, INDEX IDX_CD6F39332CF3B78B (delivery_note_id), INDEX IDX_CD6F39334584665A (product_id), INDEX IDX_CD6F39332E08A7D7 (return_note_entry_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE deposit (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, label VARCHAR(100) DEFAULT NULL, single_amount INT NOT NULL, crate_amount INT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE monthly_time_sheet (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, year SMALLINT NOT NULL, month SMALLINT NOT NULL, account_id BINARY(16) NOT NULL, UNIQUE INDEX uniq_time_sheet_account_month (account_id, year, month), INDEX IDX_B53101B9B6B5FBA (account_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE monthly_time_sheet_entry (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, day SMALLINT NOT NULL, type VARCHAR(20) NOT NULL, start TIME DEFAULT NULL, end TIME DEFAULT NULL, break_minutes INT DEFAULT NULL, note VARCHAR(500) DEFAULT NULL, time_sheet_id BINARY(16) NOT NULL, UNIQUE INDEX uniq_time_sheet_entry_day (time_sheet_id, day), INDEX IDX_7D055B9B2F974569 (time_sheet_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_preset (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_preset_item (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, order_preset_id BINARY(16) NOT NULL, product_id BINARY(16) NOT NULL, UNIQUE INDEX uniq_order_preset_item (order_preset_id, product_id), INDEX IDX_85A115293CEFF597 (order_preset_id), INDEX IDX_85A115294584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name VARCHAR(255) NOT NULL, sales_price INT DEFAULT NULL, sellable TINYINT NOT NULL, rentable TINYINT NOT NULL, quantity_in_crate INT DEFAULT NULL, archived TINYINT DEFAULT 0 NOT NULL, deposit_id BINARY(16) DEFAULT NULL, INDEX idx_product_name (name), INDEX IDX_D34A04AD9815E4B1 (deposit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_union (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_union_product (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, product_union_id BINARY(16) NOT NULL, product_id BINARY(16) NOT NULL, UNIQUE INDEX uniq_product_union_product_product (product_id), INDEX IDX_EED5BD3EE7CB3FAE (product_union_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE refresh_token (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, token_hash CHAR(64) NOT NULL, family_id BINARY(16) NOT NULL, expires_at DATETIME NOT NULL, session_expires_at DATETIME NOT NULL, rotated_at DATETIME DEFAULT NULL, revoked_at DATETIME DEFAULT NULL, account_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_C74F2195B3BC57DA (token_hash), INDEX idx_refresh_token_family (family_id), INDEX idx_refresh_token_expires (expires_at), INDEX IDX_C74F21959B6B5FBA (account_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE return_note_entry (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, returned_total INT DEFAULT NULL, returned_total_bottles INT DEFAULT NULL, returned_full INT DEFAULT NULL, returned_full_bottles INT DEFAULT NULL, delivery_note_id BINARY(16) NOT NULL, INDEX IDX_85A594952CF3B78B (delivery_note_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE time_tracking_reminder (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, enabled TINYINT NOT NULL, time TIME NOT NULL, weekdays JSON NOT NULL, last_sent_on DATE DEFAULT NULL, account_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_C90896A09B6B5FBA (account_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09E4901476 FOREIGN KEY (default_shipping_address_id) REFERENCES customer_address (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E091995CE08 FOREIGN KEY (default_billing_address_id) REFERENCES customer_address (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE customer_address ADD CONSTRAINT FK_1193CB3F9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328E9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328E4D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES customer_address (id)');
        $this->addSql('ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328E79D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES customer_address (id)');
        $this->addSql('ALTER TABLE delivery_note ADD CONSTRAINT FK_1E21328EB03A8386 FOREIGN KEY (created_by_id) REFERENCES account (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE delivery_note_product ADD CONSTRAINT FK_CD6F39332CF3B78B FOREIGN KEY (delivery_note_id) REFERENCES delivery_note (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE delivery_note_product ADD CONSTRAINT FK_CD6F39334584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE delivery_note_product ADD CONSTRAINT FK_CD6F39332E08A7D7 FOREIGN KEY (return_note_entry_id) REFERENCES return_note_entry (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE monthly_time_sheet ADD CONSTRAINT FK_B53101B9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE monthly_time_sheet_entry ADD CONSTRAINT FK_7D055B9B2F974569 FOREIGN KEY (time_sheet_id) REFERENCES monthly_time_sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_preset_item ADD CONSTRAINT FK_85A115293CEFF597 FOREIGN KEY (order_preset_id) REFERENCES order_preset (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_preset_item ADD CONSTRAINT FK_85A115294584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD9815E4B1 FOREIGN KEY (deposit_id) REFERENCES deposit (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE product_union_product ADD CONSTRAINT FK_EED5BD3EE7CB3FAE FOREIGN KEY (product_union_id) REFERENCES product_union (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_union_product ADD CONSTRAINT FK_EED5BD3E4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F21959B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE return_note_entry ADD CONSTRAINT FK_85A594952CF3B78B FOREIGN KEY (delivery_note_id) REFERENCES delivery_note (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE time_tracking_reminder ADD CONSTRAINT FK_C90896A09B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09E4901476');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E091995CE08');
        $this->addSql('ALTER TABLE customer_address DROP FOREIGN KEY FK_1193CB3F9395C3F3');
        $this->addSql('ALTER TABLE delivery_note DROP FOREIGN KEY FK_1E21328E9395C3F3');
        $this->addSql('ALTER TABLE delivery_note DROP FOREIGN KEY FK_1E21328E4D4CFF2B');
        $this->addSql('ALTER TABLE delivery_note DROP FOREIGN KEY FK_1E21328E79D0C0E4');
        $this->addSql('ALTER TABLE delivery_note DROP FOREIGN KEY FK_1E21328EB03A8386');
        $this->addSql('ALTER TABLE delivery_note_product DROP FOREIGN KEY FK_CD6F39332CF3B78B');
        $this->addSql('ALTER TABLE delivery_note_product DROP FOREIGN KEY FK_CD6F39334584665A');
        $this->addSql('ALTER TABLE delivery_note_product DROP FOREIGN KEY FK_CD6F39332E08A7D7');
        $this->addSql('ALTER TABLE monthly_time_sheet DROP FOREIGN KEY FK_B53101B9B6B5FBA');
        $this->addSql('ALTER TABLE monthly_time_sheet_entry DROP FOREIGN KEY FK_7D055B9B2F974569');
        $this->addSql('ALTER TABLE order_preset_item DROP FOREIGN KEY FK_85A115293CEFF597');
        $this->addSql('ALTER TABLE order_preset_item DROP FOREIGN KEY FK_85A115294584665A');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD9815E4B1');
        $this->addSql('ALTER TABLE product_union_product DROP FOREIGN KEY FK_EED5BD3EE7CB3FAE');
        $this->addSql('ALTER TABLE product_union_product DROP FOREIGN KEY FK_EED5BD3E4584665A');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F21959B6B5FBA');
        $this->addSql('ALTER TABLE return_note_entry DROP FOREIGN KEY FK_85A594952CF3B78B');
        $this->addSql('ALTER TABLE time_tracking_reminder DROP FOREIGN KEY FK_C90896A09B6B5FBA');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE customer_address');
        $this->addSql('DROP TABLE delivery_note');
        $this->addSql('DROP TABLE delivery_note_product');
        $this->addSql('DROP TABLE deposit');
        $this->addSql('DROP TABLE monthly_time_sheet');
        $this->addSql('DROP TABLE monthly_time_sheet_entry');
        $this->addSql('DROP TABLE order_preset');
        $this->addSql('DROP TABLE order_preset_item');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_union');
        $this->addSql('DROP TABLE product_union_product');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE return_note_entry');
        $this->addSql('DROP TABLE time_tracking_reminder');
    }
}
