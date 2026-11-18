<?php

declare(strict_types=1);

namespace App\Audit;

/**
 * Catalogue of audited actions. Values are stable identifiers stored in the audit log.
 */
final class AuditAction
{
    public const string ENTITY_CREATED = 'entity.created';
    public const string ENTITY_UPDATED = 'entity.updated';
    public const string ENTITY_DELETED = 'entity.deleted';

    public const string AUTH_LOGIN_SUCCEEDED = 'auth.login_succeeded';
    public const string AUTH_LOGIN_FAILED = 'auth.login_failed';
    public const string AUTH_LOGIN_THROTTLED = 'auth.login_throttled';
    public const string AUTH_LOGOUT = 'auth.logout';
    public const string AUTH_REFRESH_TOKEN_REUSE = 'auth.refresh_token_reuse';
    public const string AUTH_PASSWORD_CHANGED = 'auth.password_changed';
    public const string AUTH_PASSWORD_RESET = 'auth.password_reset';

    public const string SECURITY_ACCESS_DENIED = 'security.access_denied';

    public const string AUDIT_PURGED = 'audit.purged';
    public const string DATA_ANONYMIZED = 'privacy.anonymized';
    public const string RETENTION_PURGED = 'privacy.retention_purged';

    public static function category(string $action): string
    {
        return strstr($action, '.', true) ?: 'other';
    }
}
