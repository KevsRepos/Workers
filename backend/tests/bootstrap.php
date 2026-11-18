<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

new Dotenv()->bootEnv(dirname(__DIR__) . '/.env');

if ($_SERVER['APP_DEBUG'] ?? false) {
    umask(0o000);
}

// Ephemeral JWT key pair for the test run (never committed).
$keyDir = dirname(__DIR__) . '/var/jwt-test';
if (!is_file($keyDir . '/private.pem')) {
    @mkdir($keyDir, 0o700, true);
    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($key, $privatePem, $_SERVER['JWT_PASSPHRASE'] ?? 'test-passphrase');
    file_put_contents($keyDir . '/private.pem', $privatePem);
    file_put_contents($keyDir . '/public.pem', openssl_pkey_get_details($key)['key']);
}

// Fresh test database with the real migrations (incl. audit triggers).
if (!getenv('SKIP_DB_SETUP')) {
    $console = escapeshellarg(dirname(__DIR__) . '/bin/console');
    foreach ([
        'doctrine:database:drop --force --if-exists',
        'doctrine:database:create',
        'doctrine:migrations:migrate --no-interaction --allow-no-migration',
    ] as $command) {
        passthru(sprintf('php %s %s --env=test --quiet', $console, $command), $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException('Test database setup failed: ' . $command);
        }
    }
}
