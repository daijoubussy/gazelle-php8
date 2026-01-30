<?php

declare(strict_types=1);

namespace Gazelle\Tests\Acceptance\Bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Gazelle\Tests\Acceptance\Support\Helpers\TestClock;
use Gazelle\Tests\Acceptance\Support\Helpers\DeterministicFaker;
use Gazelle\Tests\Acceptance\Support\Helpers\TestDatabase;
use PHPUnit\Framework\Assert;

/**
 * Base Feature Context for all Behat tests
 *
 * Provides common functionality and ensures deterministic test execution:
 * - Frozen clock for time-dependent operations
 * - Seeded faker for reproducible random data
 * - Database transaction rollback between scenarios
 * - No external service calls (all mocked)
 */
class FeatureContext implements Context
{
    protected TestClock $clock;
    protected DeterministicFaker $faker;
    protected TestDatabase $database;

    /** @var array<string, mixed> Shared state between steps */
    protected array $context = [];

    /** @var array<string, mixed> Results from operations */
    protected array $results = [];

    /** @var array<string> Errors collected during scenario */
    protected array $errors = [];

    public function __construct()
    {
        // Initialize with deterministic defaults
        $this->clock = new TestClock('2024-01-15 10:00:00');
        $this->faker = new DeterministicFaker(seed: 12345);
        $this->database = new TestDatabase();
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(BeforeScenarioScope $scope): void
    {
        // Reset all state
        $this->context = [];
        $this->results = [];
        $this->errors = [];

        // Reset clock to default
        $this->clock->freeze('2024-01-15 10:00:00');

        // Reset faker seed for reproducibility
        $this->faker->reset();

        // Begin database transaction (will be rolled back after scenario)
        $this->database->beginTransaction();
    }

    /**
     * @AfterScenario
     */
    public function afterScenario(AfterScenarioScope $scope): void
    {
        // Rollback database changes
        $this->database->rollback();

        // Clear any static state that might leak
        $this->clearStaticState();
    }

    /**
     * @Given the current time is :datetime
     */
    public function theCurrentTimeIs(string $datetime): void
    {
        $this->clock->freeze($datetime);
    }

    /**
     * @Given :seconds seconds have passed
     */
    public function secondsHavePassed(int $seconds): void
    {
        $this->clock->advance($seconds);
    }

    /**
     * @Given the system is in a clean state
     */
    public function theSystemIsInACleanState(): void
    {
        $this->database->truncateAll();
        $this->context = [];
    }

    /**
     * @Then I should see no errors
     */
    public function iShouldSeeNoErrors(): void
    {
        Assert::assertEmpty(
            $this->errors,
            sprintf('Expected no errors, but got: %s', implode(', ', $this->errors))
        );
    }

    /**
     * @Then I should see error :message
     */
    public function iShouldSeeError(string $message): void
    {
        Assert::assertContains(
            $message,
            $this->errors,
            sprintf('Expected error "%s" not found in: %s', $message, implode(', ', $this->errors))
        );
    }

    /**
     * @Then the operation should succeed
     */
    public function theOperationShouldSucceed(): void
    {
        $lastResult = end($this->results);
        Assert::assertNotFalse($lastResult, 'No operation result found');
        Assert::assertTrue(
            $lastResult['success'] ?? false,
            sprintf('Operation failed: %s', $lastResult['error'] ?? 'Unknown error')
        );
    }

    /**
     * @Then the operation should fail
     */
    public function theOperationShouldFail(): void
    {
        $lastResult = end($this->results);
        Assert::assertNotFalse($lastResult, 'No operation result found');
        Assert::assertFalse(
            $lastResult['success'] ?? true,
            'Expected operation to fail, but it succeeded'
        );
    }

    /**
     * @Then the operation should fail with :error
     */
    public function theOperationShouldFailWith(string $error): void
    {
        $lastResult = end($this->results);
        Assert::assertNotFalse($lastResult, 'No operation result found');
        Assert::assertFalse($lastResult['success'] ?? true, 'Expected operation to fail');
        Assert::assertStringContainsString(
            $error,
            $lastResult['error'] ?? '',
            sprintf('Expected error containing "%s", got: %s', $error, $lastResult['error'] ?? 'none')
        );
    }

    /**
     * Store a value in the shared context
     */
    protected function set(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    /**
     * Get a value from the shared context
     */
    protected function get(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }

    /**
     * Store an operation result
     */
    protected function recordResult(bool $success, ?string $error = null, array $data = []): void
    {
        $this->results[] = [
            'success' => $success,
            'error' => $error,
            'data' => $data,
            'timestamp' => $this->clock->now(),
        ];

        if (!$success && $error) {
            $this->errors[] = $error;
        }
    }

    /**
     * Get the last recorded result
     */
    protected function lastResult(): ?array
    {
        $result = end($this->results);
        return $result === false ? null : $result;
    }

    /**
     * Clear any static state that might leak between tests
     */
    private function clearStaticState(): void
    {
        // Add any static state resets here
        // Example: SomeClass::reset();
    }
}
