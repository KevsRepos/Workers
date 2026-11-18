<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Append-only, hash-chained audit log and gap-free document number sequences.
 *
 * Integrity measures:
 *  - Triggers reject UPDATE on audit_log and DELETE unless the retention purge explicitly
 *    enables it for its session (@audit_purge_allowed = 1).
 *  - audit_chain_head holds the last sequence number and hash; it is locked for every append.
 */
final class Version20261117000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Audit log (append-only, hash chain) and document sequences';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE audit_log (
                seq BIGINT UNSIGNED NOT NULL,
                event_id CHAR(36) NOT NULL,
                occurred_at DATETIME NOT NULL,
                category VARCHAR(32) NOT NULL,
                action VARCHAR(64) NOT NULL,
                actor_id CHAR(36) DEFAULT NULL,
                actor_label VARCHAR(255) DEFAULT NULL,
                subject_type VARCHAR(64) DEFAULT NULL,
                subject_id VARCHAR(64) DEFAULT NULL,
                changes LONGTEXT DEFAULT NULL,
                context LONGTEXT DEFAULT NULL,
                prev_hash CHAR(64) NOT NULL,
                hash CHAR(64) NOT NULL,
                PRIMARY KEY (seq),
                UNIQUE INDEX uniq_audit_log_event (event_id),
                INDEX idx_audit_log_occurred (occurred_at),
                INDEX idx_audit_log_subject (subject_type, subject_id),
                INDEX idx_audit_log_actor (actor_id),
                INDEX idx_audit_log_action (action)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE = InnoDB
            SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE audit_chain_head (
                id TINYINT UNSIGNED NOT NULL,
                last_seq BIGINT UNSIGNED NOT NULL,
                last_hash CHAR(64) NOT NULL,
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE = InnoDB
            SQL);
        $this->addSql("INSERT INTO audit_chain_head (id, last_seq, last_hash) VALUES (1, 0, REPEAT('0', 64))");

        $this->addSql(<<<'SQL'
            CREATE TRIGGER audit_log_no_update BEFORE UPDATE ON audit_log FOR EACH ROW
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_log is append-only'
            SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER audit_log_no_delete BEFORE DELETE ON audit_log FOR EACH ROW
            BEGIN
                IF @audit_purge_allowed IS NULL OR @audit_purge_allowed <> 1 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_log is append-only';
                END IF;
            END
            SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER audit_chain_head_no_delete BEFORE DELETE ON audit_chain_head FOR EACH ROW
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_chain_head must not be deleted'
            SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE document_sequence (
                name VARCHAR(64) NOT NULL,
                year SMALLINT UNSIGNED NOT NULL,
                last_value INT UNSIGNED NOT NULL,
                PRIMARY KEY (name, year)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('The audit log must not be dropped.');
    }
}
