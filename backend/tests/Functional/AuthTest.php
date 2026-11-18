<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Audit\AuditAction;

final class AuthTest extends ApiTestCase
{
    public function testLoginReturnsTokenPairAndProfile(): void
    {
        $this->createAccount('anna@example.org');

        $body = $this->request('POST', '/api/v1/auth/login', ['email' => 'Anna@Example.org ', 'password' => self::PASSWORD]);

        $this->assertStatus(200);
        self::assertIsString($body['accessToken']);
        self::assertIsString($body['refreshToken']);
        self::assertSame('anna@example.org', $body['account']['emailAddress']);
        self::assertSame(['ROLE_USER'], $body['account']['roles']);
        self::assertCount(1, $this->auditRows(AuditAction::AUTH_LOGIN_SUCCEEDED));
    }

    public function testLoginWithWrongPasswordIsRejectedUniformly(): void
    {
        $this->createAccount('anna@example.org');

        $wrongPassword = $this->request('POST', '/api/v1/auth/login', ['email' => 'anna@example.org', 'password' => 'nope']);
        $this->assertProblem(401, 'invalid-credentials', $wrongPassword);

        $unknownUser = $this->request('POST', '/api/v1/auth/login', ['email' => 'ghost@example.org', 'password' => 'nope']);
        $this->assertProblem(401, 'invalid-credentials', $unknownUser);
        self::assertSame($wrongPassword['detail'], $unknownUser['detail'], 'No user enumeration');

        self::assertCount(2, $this->auditRows(AuditAction::AUTH_LOGIN_FAILED));
    }

    public function testDisabledAccountCannotLogIn(): void
    {
        $this->createAccount('gone@example.org', active: false);

        $body = $this->request('POST', '/api/v1/auth/login', ['email' => 'gone@example.org', 'password' => self::PASSWORD]);

        $this->assertProblem(401, 'invalid-credentials', $body);
    }

    public function testLoginIsThrottledAfterRepeatedFailures(): void
    {
        $this->client->disableReboot();
        $this->createAccount('anna@example.org');

        for ($i = 0; $i < 5; ++$i) {
            $this->request('POST', '/api/v1/auth/login', ['email' => 'anna@example.org', 'password' => 'wrong']);
            $this->assertStatus(401);
        }

        $body = $this->request('POST', '/api/v1/auth/login', ['email' => 'anna@example.org', 'password' => self::PASSWORD]);
        $this->assertProblem(429, 'too-many-requests', $body);
        self::assertCount(1, $this->auditRows(AuditAction::AUTH_LOGIN_THROTTLED));
    }

    public function testProtectedEndpointRequiresToken(): void
    {
        $body = $this->request('GET', '/api/v1/auth/me');
        $this->assertProblem(401, 'unauthenticated', $body);

        $body = $this->request('GET', '/api/v1/auth/me', token: 'garbage');
        $this->assertProblem(401, 'unauthenticated', $body);
    }

    public function testMeReturnsCurrentAccount(): void
    {
        $token = $this->tokenFor('anna@example.org');

        $body = $this->request('GET', '/api/v1/auth/me', token: $token);

        $this->assertStatus(200);
        self::assertSame('anna@example.org', $body['emailAddress']);
        self::assertArrayNotHasKey('password', $body);
    }

    public function testRefreshRotatesTokenAndDetectsReuse(): void
    {
        $this->createAccount('anna@example.org');
        $first = $this->login('anna@example.org');

        $second = $this->request('POST', '/api/v1/auth/refresh', ['refreshToken' => $first['refreshToken']]);
        $this->assertStatus(200);
        self::assertNotSame($first['refreshToken'], $second['refreshToken']);

        // Replaying the rotated token is treated as theft: the whole session is revoked.
        $replay = $this->request('POST', '/api/v1/auth/refresh', ['refreshToken' => $first['refreshToken']]);
        $this->assertProblem(401, 'invalid-refresh-token', $replay);
        self::assertCount(1, $this->auditRows(AuditAction::AUTH_REFRESH_TOKEN_REUSE));

        $legit = $this->request('POST', '/api/v1/auth/refresh', ['refreshToken' => $second['refreshToken']]);
        $this->assertProblem(401, 'invalid-refresh-token', $legit);
    }

    public function testExpiredRefreshTokenIsRejected(): void
    {
        $this->createAccount('anna@example.org');
        $tokens = $this->login('anna@example.org');
        $this->em()->getConnection()->executeStatement('UPDATE refresh_token SET expires_at = ?', ['2000-01-01 00:00:00']);

        $body = $this->request('POST', '/api/v1/auth/refresh', ['refreshToken' => $tokens['refreshToken']]);

        $this->assertProblem(401, 'invalid-refresh-token', $body);
    }

    public function testLogoutRevokesSession(): void
    {
        $this->createAccount('anna@example.org');
        $tokens = $this->login('anna@example.org');

        $this->request('POST', '/api/v1/auth/logout', ['refreshToken' => $tokens['refreshToken']]);
        $this->assertStatus(204);

        $body = $this->request('POST', '/api/v1/auth/refresh', ['refreshToken' => $tokens['refreshToken']]);
        $this->assertProblem(401, 'invalid-refresh-token', $body);
        self::assertCount(1, $this->auditRows(AuditAction::AUTH_LOGOUT));
    }

    public function testChangePasswordKeepsCurrentSessionAndRevokesOthers(): void
    {
        $this->createAccount('anna@example.org');
        $current = $this->login('anna@example.org');
        $other = $this->login('anna@example.org');

        $this->request('POST', '/api/v1/auth/change-password', [
            'currentPassword' => self::PASSWORD,
            'newPassword' => 'a much better passphrase 2026',
            'confirmNewPassword' => 'a much better passphrase 2026',
            'refreshToken' => $current['refreshToken'],
        ], $current['accessToken']);
        $this->assertStatus(204);

        $this->request('POST', '/api/v1/auth/refresh', ['refreshToken' => $other['refreshToken']]);
        $this->assertStatus(401);
        $this->request('POST', '/api/v1/auth/refresh', ['refreshToken' => $current['refreshToken']]);
        $this->assertStatus(200);

        $this->login('anna@example.org', 'a much better passphrase 2026');
        self::assertCount(1, $this->auditRows(AuditAction::AUTH_PASSWORD_CHANGED));
    }

    public function testChangePasswordValidatesPolicyAndCurrentPassword(): void
    {
        $token = $this->tokenFor('anna@example.org');

        $weak = $this->request('POST', '/api/v1/auth/change-password', [
            'currentPassword' => self::PASSWORD, 'newPassword' => 'short', 'confirmNewPassword' => 'short',
        ], $token);
        $this->assertProblem(422, 'validation-failed', $weak);
        self::assertContains('newPassword', array_column($weak['errors'], 'field'));

        $wrongCurrent = $this->request('POST', '/api/v1/auth/change-password', [
            'currentPassword' => 'wrong', 'newPassword' => 'a much better passphrase 2026', 'confirmNewPassword' => 'a much better passphrase 2026',
        ], $token);
        $this->assertProblem(422, 'business-rule-violation', $wrongCurrent);
    }

    public function testDeactivatedAccountLosesAccessImmediately(): void
    {
        $account = $this->createAccount('anna@example.org');
        $token = $this->login('anna@example.org')['accessToken'];
        $this->em()->getConnection()->executeStatement('UPDATE account SET active = 0');

        $body = $this->request('GET', '/api/v1/auth/me', token: $token);

        $this->assertStatus(401);
        self::assertNotNull($account->id);
        self::assertIsArray($body);
    }
}
