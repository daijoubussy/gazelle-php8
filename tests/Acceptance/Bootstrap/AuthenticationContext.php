<?php

declare(strict_types=1);

namespace Gazelle\Tests\Acceptance\Bootstrap;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Gazelle\Tests\Acceptance\Support\Helpers\TestClock;
use Gazelle\Tests\Acceptance\Support\Helpers\DeterministicFaker;
use Gazelle\Tests\Acceptance\Support\Helpers\TestDatabase;
use Gazelle\Tests\Acceptance\Support\Fixtures\UserFixture;

/**
 * Authentication-specific step definitions
 *
 * Covers user login, logout, session management, and related features.
 */
class AuthenticationContext implements Context
{
    private TestClock $clock;
    private DeterministicFaker $faker;
    private TestDatabase $database;

    /** @var array<string, mixed> Current user data */
    private ?array $currentUser = null;

    /** @var array<string, mixed> Authentication result */
    private ?array $authResult = null;

    /** @var array<string, mixed> Session data */
    private ?array $session = null;

    /** @var int Number of login attempts */
    private int $loginAttempts = 0;

    public function __construct()
    {
        $this->clock = new TestClock();
        $this->faker = new DeterministicFaker();
        $this->database = new TestDatabase();
    }

    // ========================================
    // GIVEN Steps - Preconditions
    // ========================================

    /**
     * @Given I am a guest user
     */
    public function iAmAGuestUser(): void
    {
        $this->currentUser = null;
        $this->session = null;
    }

    /**
     * @Given I am a registered user
     */
    public function iAmARegisteredUser(): void
    {
        $this->currentUser = UserFixture::createRegisteredUser($this->database, $this->faker);
    }

    /**
     * @Given I am a registered user with email :email
     */
    public function iAmARegisteredUserWithEmail(string $email): void
    {
        $this->currentUser = UserFixture::createRegisteredUser($this->database, $this->faker, [
            'email' => $email,
        ]);
    }

    /**
     * @Given I am logged in
     */
    public function iAmLoggedIn(): void
    {
        if ($this->currentUser === null) {
            $this->iAmARegisteredUser();
        }

        $this->session = $this->createSession($this->currentUser['id']);
    }

    /**
     * @Given I am logged in as a :role
     */
    public function iAmLoggedInAsA(string $role): void
    {
        $this->currentUser = UserFixture::createUserWithRole($this->database, $this->faker, $role);
        $this->session = $this->createSession($this->currentUser['id']);
    }

    /**
     * @Given my account is disabled
     */
    public function myAccountIsDisabled(): void
    {
        if ($this->currentUser === null) {
            $this->iAmARegisteredUser();
        }

        $this->database->update('users_main', ['Enabled' => '0'], ['ID' => $this->currentUser['id']]);
        $this->currentUser['enabled'] = false;
    }

    /**
     * @Given my account is banned
     */
    public function myAccountIsBanned(): void
    {
        if ($this->currentUser === null) {
            $this->iAmARegisteredUser();
        }

        $this->database->update('users_main', ['Enabled' => '2'], ['ID' => $this->currentUser['id']]);
        $this->currentUser['enabled'] = false;
        $this->currentUser['banned'] = true;
    }

    /**
     * @Given I have exceeded the login rate limit
     */
    public function iHaveExceededTheLoginRateLimit(): void
    {
        // Simulate 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->recordLoginAttempt(false);
        }
    }

    /**
     * @Given my session has expired
     */
    public function mySessionHasExpired(): void
    {
        if ($this->session === null) {
            $this->iAmLoggedIn();
        }

        // Move time forward past session expiry
        $this->clock->advanceDays(31);
    }

    // ========================================
    // WHEN Steps - Actions
    // ========================================

    /**
     * @When I submit valid login credentials
     */
    public function iSubmitValidLoginCredentials(): void
    {
        Assert::assertNotNull($this->currentUser, 'No user context set');

        $this->authResult = $this->attemptLogin(
            $this->currentUser['email'],
            'TestPassword123!' // Default test password
        );
    }

    /**
     * @When I submit login credentials with email :email and password :password
     */
    public function iSubmitLoginCredentialsWithEmailAndPassword(string $email, string $password): void
    {
        $this->authResult = $this->attemptLogin($email, $password);
    }

    /**
     * @When I submit invalid login credentials
     */
    public function iSubmitInvalidLoginCredentials(): void
    {
        $email = $this->currentUser['email'] ?? $this->faker->email();
        $this->authResult = $this->attemptLogin($email, 'WrongPassword123!');
    }

    /**
     * @When I submit login with wrong password :times times
     */
    public function iSubmitLoginWithWrongPasswordTimes(int $times): void
    {
        $email = $this->currentUser['email'] ?? $this->faker->email();

        for ($i = 0; $i < $times; $i++) {
            $this->authResult = $this->attemptLogin($email, 'WrongPassword' . $i);
        }
    }

    /**
     * @When I log out
     */
    public function iLogOut(): void
    {
        if ($this->session !== null) {
            $this->destroySession($this->session['id']);
            $this->session = null;
        }

        $this->authResult = ['success' => true, 'action' => 'logout'];
    }

    /**
     * @When I request a password reset for :email
     */
    public function iRequestAPasswordResetFor(string $email): void
    {
        $this->authResult = $this->requestPasswordReset($email);
    }

    /**
     * @When I access a protected resource
     */
    public function iAccessAProtectedResource(): void
    {
        $this->authResult = $this->checkAccess('/user/profile');
    }

    // ========================================
    // THEN Steps - Assertions
    // ========================================

    /**
     * @Then I should be authenticated
     */
    public function iShouldBeAuthenticated(): void
    {
        Assert::assertNotNull($this->authResult, 'No authentication result');
        Assert::assertTrue(
            $this->authResult['success'] ?? false,
            sprintf('Authentication failed: %s', $this->authResult['error'] ?? 'Unknown')
        );
        Assert::assertNotNull($this->authResult['session'] ?? null, 'No session created');
    }

    /**
     * @Then I should not be authenticated
     */
    public function iShouldNotBeAuthenticated(): void
    {
        Assert::assertNotNull($this->authResult, 'No authentication result');
        Assert::assertFalse(
            $this->authResult['success'] ?? true,
            'Authentication should have failed'
        );
    }

    /**
     * @Then I should see authentication error :message
     */
    public function iShouldSeeAuthenticationError(string $message): void
    {
        Assert::assertNotNull($this->authResult, 'No authentication result');
        Assert::assertFalse($this->authResult['success'] ?? true);
        Assert::assertStringContainsString(
            $message,
            $this->authResult['error'] ?? '',
            sprintf('Expected error "%s", got: %s', $message, $this->authResult['error'] ?? 'none')
        );
    }

    /**
     * @Then a session should be created
     */
    public function aSessionShouldBeCreated(): void
    {
        Assert::assertNotNull(
            $this->authResult['session'] ?? null,
            'No session was created'
        );
    }

    /**
     * @Then the session should expire in :days days
     */
    public function theSessionShouldExpireInDays(int $days): void
    {
        Assert::assertNotNull($this->authResult['session'] ?? null);

        $expiry = $this->authResult['session']['expires_at'] ?? null;
        Assert::assertNotNull($expiry, 'No session expiry set');

        $expectedExpiry = $this->clock->now()->modify("+{$days} days");
        $actualExpiry = new \DateTimeImmutable($expiry);

        // Allow 1 minute tolerance
        $diffSeconds = abs($expectedExpiry->getTimestamp() - $actualExpiry->getTimestamp());
        Assert::assertLessThan(60, $diffSeconds, 'Session expiry time mismatch');
    }

    /**
     * @Then I should be rate limited
     */
    public function iShouldBeRateLimited(): void
    {
        Assert::assertNotNull($this->authResult, 'No authentication result');
        Assert::assertFalse($this->authResult['success'] ?? true);
        Assert::assertTrue(
            $this->authResult['rate_limited'] ?? false,
            'Expected to be rate limited'
        );
    }

    /**
     * @Then I should be redirected to login
     */
    public function iShouldBeRedirectedToLogin(): void
    {
        Assert::assertNotNull($this->authResult, 'No access check result');
        Assert::assertFalse($this->authResult['success'] ?? true);
        Assert::assertEquals('login_required', $this->authResult['redirect'] ?? null);
    }

    /**
     * @Then a password reset email should be sent
     */
    public function aPasswordResetEmailShouldBeSent(): void
    {
        Assert::assertNotNull($this->authResult, 'No password reset result');
        Assert::assertTrue(
            $this->authResult['email_sent'] ?? false,
            'Password reset email was not sent'
        );
    }

    /**
     * @Then my login attempt should be logged
     */
    public function myLoginAttemptShouldBeLogged(): void
    {
        $logs = $this->database->findAll('login_attempts', [
            'ip_address' => '127.0.0.1', // Test IP
        ]);

        Assert::assertNotEmpty($logs, 'No login attempts logged');
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Simulate login attempt
     *
     * @return array<string, mixed>
     */
    private function attemptLogin(string $email, string $password): array
    {
        $this->loginAttempts++;

        // Check rate limiting (5 attempts per minute)
        $recentAttempts = $this->getRecentLoginAttempts();
        if ($recentAttempts >= 5) {
            return [
                'success' => false,
                'error' => 'Too many login attempts',
                'rate_limited' => true,
            ];
        }

        // Find user
        $user = $this->database->find('users_main', ['Email' => $email]);

        if ($user === null) {
            $this->recordLoginAttempt(false);
            return [
                'success' => false,
                'error' => 'Invalid credentials',
            ];
        }

        // Check if account is enabled
        if ($user['Enabled'] !== '1') {
            $this->recordLoginAttempt(false);
            return [
                'success' => false,
                'error' => 'Account is disabled',
            ];
        }

        // Verify password (simplified for tests)
        $expectedHash = $user['PassHash'] ?? '';
        if (!password_verify($password, $expectedHash)) {
            $this->recordLoginAttempt(false);
            return [
                'success' => false,
                'error' => 'Invalid credentials',
            ];
        }

        // Success - create session
        $this->recordLoginAttempt(true);
        $session = $this->createSession((int) $user['ID']);

        return [
            'success' => true,
            'user_id' => (int) $user['ID'],
            'session' => $session,
        ];
    }

    /**
     * Create a session for user
     *
     * @return array<string, mixed>
     */
    private function createSession(int $userId): array
    {
        $sessionId = bin2hex(random_bytes(32));
        $expiresAt = $this->clock->now()->modify('+30 days');

        $this->database->insert('users_sessions', [
            'UserID' => $userId,
            'SessionID' => $sessionId,
            'IP' => '127.0.0.1',
            'LastUpdate' => $this->clock->format('Y-m-d H:i:s'),
        ]);

        return [
            'id' => $sessionId,
            'user_id' => $userId,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Destroy a session
     */
    private function destroySession(string $sessionId): void
    {
        $this->database->delete('users_sessions', ['SessionID' => $sessionId]);
    }

    /**
     * Record login attempt for rate limiting
     */
    private function recordLoginAttempt(bool $success): void
    {
        $this->database->insert('login_attempts', [
            'ip_address' => '127.0.0.1',
            'success' => $success ? 1 : 0,
            'attempted_at' => $this->clock->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get count of recent login attempts
     */
    private function getRecentLoginAttempts(): int
    {
        $oneMinuteAgo = $this->clock->now()->modify('-1 minute')->format('Y-m-d H:i:s');

        $attempts = $this->database->query(
            'SELECT COUNT(*) as count FROM login_attempts WHERE ip_address = ? AND attempted_at > ?',
            ['127.0.0.1', $oneMinuteAgo]
        );

        return (int) ($attempts[0]['count'] ?? 0);
    }

    /**
     * Request password reset
     *
     * @return array<string, mixed>
     */
    private function requestPasswordReset(string $email): array
    {
        $user = $this->database->find('users_main', ['Email' => $email]);

        // Always return success to prevent email enumeration
        return [
            'success' => true,
            'email_sent' => $user !== null,
        ];
    }

    /**
     * Check access to protected resource
     *
     * @return array<string, mixed>
     */
    private function checkAccess(string $path): array
    {
        if ($this->session === null) {
            return [
                'success' => false,
                'redirect' => 'login_required',
            ];
        }

        // Check if session is still valid
        $sessionRecord = $this->database->find('users_sessions', [
            'SessionID' => $this->session['id'],
        ]);

        if ($sessionRecord === null) {
            return [
                'success' => false,
                'redirect' => 'login_required',
            ];
        }

        return [
            'success' => true,
            'path' => $path,
        ];
    }
}
