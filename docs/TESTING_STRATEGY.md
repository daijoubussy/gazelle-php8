# Gazelle PHP 8.5 Testing Strategy

## Executive Summary

This document outlines the Behavior-Driven Development (BDD) testing strategy for the Gazelle PHP 8.5 modernization project. All tests must be **100% deterministic** - no flaky tests allowed.

---

## Long-Term Project Alignment

### Project Phases

| Phase | Focus | Testing Requirement |
|-------|-------|---------------------|
| **Phase 0** | Test Infrastructure (CURRENT) | Set up all test harnesses and BDD framework |
| **Phase 1** | Domain Layer | Unit tests for entities, value objects, domain events |
| **Phase 2** | Application Layer | Integration tests for services, command/query handlers |
| **Phase 3** | Infrastructure Layer | Integration tests for repositories, cache, external services |
| **Phase 4** | HTTP/API Layer | E2E tests for controllers, middleware, routes |
| **Phase 5** | Frontend Components | Component tests for React pages and components |
| **Phase 6** | Full Stack E2E | End-to-end user journey tests |
| **Phase 7** | Legacy Migration | Tests for migrated legacy classes |

### Dependencies & Blockers

```
Phase 0 (Infrastructure) ─┬──► Phase 1 (Domain)
                          │
                          └──► Phase 5 (Frontend Unit)

Phase 1 (Domain) ──────────► Phase 2 (Application)

Phase 2 (Application) ─────► Phase 3 (Infrastructure)

Phase 3 (Infrastructure) ──► Phase 4 (HTTP/API)

Phase 4 + Phase 5 ─────────► Phase 6 (Full Stack E2E)

Phase 1-4 ─────────────────► Phase 7 (Legacy Migration)
```

---

## Internal Kanban Board

### Phase 0: Test Infrastructure

| Backlog | In Progress | Review | Done |
|---------|-------------|--------|------|
| | Create testing strategy doc | | Research BDD frameworks |
| Set up Behat for PHP | | | Audit existing tests |
| Set up Playwright for TS | | | |
| Create mock factories | | | |
| Create test fixtures | | | |
| Configure CI test pipeline | | | |
| Write user story templates | | | |

### Phase 1: Domain Layer Tests (Blocked by Phase 0)

| Backlog | In Progress | Review | Done |
|---------|-------------|--------|------|
| User entity tests | | | |
| Torrent entity tests | | | |
| Forum entity tests | | | |
| Permission entity tests | | | |
| Value object tests | | | |
| Domain event tests | | | |

### Phase 2-7: (Not Started)

*Will be populated as Phase 0/1 complete*

---

## Test Pyramid Distribution

```
                    ┌─────────────┐
                    │   E2E BDD   │  10% (~50 scenarios)
                    │  Playwright │
                    │  +Cucumber  │
                    ├─────────────┤
                    │ Integration │  20% (~200 tests)
                    │   Behat +   │
                    │  Component  │
        ┌───────────┴─────────────┴───────────┐
        │           Unit Tests                │  70% (~700 tests)
        │     PHPUnit + Vitest/Jest           │
        │                                     │
        └─────────────────────────────────────┘
```

### Test Count Targets

| Layer | PHP Tests | Frontend Tests | Total |
|-------|-----------|----------------|-------|
| Unit | ~400 | ~300 | ~700 |
| Integration | ~150 | ~50 | ~200 |
| E2E | ~30 | ~20 | ~50 |
| **Total** | ~580 | ~370 | **~950** |

---

## Tool Stack

### PHP Backend

| Tool | Purpose | Version |
|------|---------|---------|
| **PHPUnit** | Unit & Integration tests | 11.0 |
| **Behat** | BDD Acceptance tests | 3.14+ |
| **Mockery** | Mocking framework | 1.6+ |
| **Faker** | Test data generation | 1.23+ |
| **PHPStan** | Static analysis | 1.10 (existing) |

### TypeScript/React Frontend

| Tool | Purpose | Version |
|------|---------|---------|
| **Vitest** | Unit tests | 2.0 (existing) |
| **Playwright** | E2E & Component tests | 1.40+ |
| **@cucumber/cucumber** | Gherkin BDD | 10.0+ |
| **@testing-library/react** | Component testing | 14.0+ |
| **MSW** | API mocking | 2.0+ |

---

## Deterministic Test Requirements

### Mandatory Rules

1. **No Random Data Without Seeds**
   ```php
   // BAD
   $email = fake()->email();

   // GOOD
   $faker = Faker\Factory::create();
   $faker->seed(12345);
   $email = $faker->email(); // Always same result
   ```

2. **No Time-Dependent Tests**
   ```php
   // BAD
   $this->assertEquals(date('Y-m-d'), $result);

   // GOOD - Use clock injection
   $clock = new FrozenClock('2024-01-15 10:30:00');
   $service = new MyService($clock);
   $this->assertEquals('2024-01-15', $result);
   ```

3. **No External Service Calls**
   ```php
   // BAD
   $response = $httpClient->get('https://api.example.com');

   // GOOD - Mock all external dependencies
   $httpClient = $this->createMock(HttpClientInterface::class);
   $httpClient->method('get')->willReturn(new Response(200, $fixtureData));
   ```

4. **Database Isolation**
   ```php
   // Each test gets fresh database state
   // Use transactions that rollback after each test
   // Or use in-memory SQLite for unit tests
   ```

5. **No Shared State Between Tests**
   ```php
   // BAD - Static property persists
   MyClass::$cache = [];

   // GOOD - Reset in setUp/tearDown
   protected function setUp(): void {
       MyClass::resetState();
   }
   ```

6. **Explicit Waits, Never Sleep**
   ```typescript
   // BAD
   await new Promise(r => setTimeout(r, 3000));

   // GOOD
   await expect(page.locator('.result')).toBeVisible({ timeout: 5000 });
   ```

### Test Isolation Checklist

- [ ] Test can run independently
- [ ] Test can run in any order
- [ ] Test produces same result every time
- [ ] Test doesn't depend on system clock
- [ ] Test doesn't call external APIs
- [ ] Test cleans up its own state
- [ ] Test uses mocked randomness (seeded faker)

---

## User Story Template

### Format

```markdown
## US-[ID]: [Title]

**As a** [role]
**I want** [feature/capability]
**So that** [benefit/value]

### Acceptance Criteria

1. [Criterion 1]
2. [Criterion 2]
3. [Criterion 3]

### Technical Notes

- [Implementation consideration]
- [Edge cases to handle]

### Gherkin Scenarios

See: `tests/acceptance/features/[feature-name].feature`
```

### Example User Story

```markdown
## US-001: User Authentication

**As a** registered user
**I want** to log in with my credentials
**So that** I can access my account and download torrents

### Acceptance Criteria

1. User can log in with valid email and password
2. User sees error message for invalid credentials
3. User is redirected to dashboard after successful login
4. Session is created with 30-day expiry
5. Failed login attempts are rate-limited (5 per minute)

### Technical Notes

- Password verified using bcrypt
- Session stored in database + cache
- Rate limiting uses IP + email combination

### Gherkin Scenarios

See: `tests/acceptance/features/authentication.feature`
```

---

## Gherkin Feature File Structure

### Directory Layout

```
tests/
├── acceptance/
│   └── features/
│       ├── authentication/
│       │   ├── login.feature
│       │   ├── logout.feature
│       │   └── password_reset.feature
│       ├── torrents/
│       │   ├── browse.feature
│       │   ├── download.feature
│       │   ├── upload.feature
│       │   └── search.feature
│       ├── user/
│       │   ├── profile.feature
│       │   ├── settings.feature
│       │   └── notifications.feature
│       └── forums/
│           ├── browse_forums.feature
│           ├── create_post.feature
│           └── moderation.feature
├── bootstrap/
│   └── FeatureContext.php
└── support/
    ├── fixtures/
    └── helpers/
```

### Feature File Template

```gherkin
@tag1 @tag2
Feature: [Feature Name]
  [Optional description paragraph]

  As a [role]
  I want [feature]
  So that [benefit]

  Background:
    Given [common precondition for all scenarios]

  @smoke @critical
  Scenario: [Scenario Name - Happy Path]
    Given [precondition]
    And [additional precondition]
    When [action]
    And [additional action]
    Then [expected outcome]
    And [additional outcome]

  @edge-case
  Scenario: [Scenario Name - Edge Case]
    Given [precondition]
    When [action]
    Then [expected outcome]

  @data-driven
  Scenario Outline: [Parameterized Scenario]
    Given I am a user with role "<role>"
    When I attempt to "<action>"
    Then I should see "<result>"

    Examples:
      | role    | action        | result          |
      | guest   | download      | login required  |
      | member  | download      | success         |
      | banned  | download      | access denied   |
```

### Gherkin Best Practices

#### DO:
- One scenario = one specific behavior
- Keep scenarios to 3-7 steps
- Use domain language, not UI language
- Make steps reusable across features
- Use Background for shared setup
- Tag scenarios for selective execution

#### DON'T:
- Don't include implementation details
- Don't chain multiple Thens without When
- Don't test UI mechanics (click button #xyz)
- Don't use conditional logic in steps
- Don't hard-code test data in scenarios

### Step Definition Patterns

```php
// Authentication steps
Given('I am a registered user', function() {
    $this->user = UserFactory::create();
});

Given('I am logged in as {string}', function(string $role) {
    $this->user = UserFactory::createWithRole($role);
    $this->session = $this->authService->login($this->user);
});

When('I submit valid login credentials', function() {
    $this->result = $this->authService->attempt(
        $this->user->email,
        'password123'
    );
});

Then('I should be authenticated', function() {
    Assert::assertTrue($this->result->isSuccess());
    Assert::assertNotNull($this->result->getSession());
});

Then('I should see error {string}', function(string $message) {
    Assert::assertFalse($this->result->isSuccess());
    Assert::assertEquals($message, $this->result->getError());
});
```

---

## Test Categories & Tags

### PHP Tests

| Tag | Description | Run Frequency |
|-----|-------------|---------------|
| `@unit` | Fast, isolated unit tests | Every commit |
| `@integration` | Database/service integration | Every PR |
| `@acceptance` | Full BDD scenarios | Every PR |
| `@smoke` | Critical path tests | Every deployment |
| `@slow` | Tests >1s execution | Nightly |
| `@flaky` | Quarantined flaky tests | Manual only |

### Frontend Tests

| Tag | Description | Run Frequency |
|-----|-------------|---------------|
| `@unit` | Component unit tests | Every commit |
| `@component` | Playwright component tests | Every PR |
| `@e2e` | Full browser tests | Every PR |
| `@visual` | Screenshot comparison | Weekly |
| `@a11y` | Accessibility tests | Every PR |

---

## CI/CD Pipeline Integration

### Test Execution Order

```yaml
# .github/workflows/test.yml

jobs:
  lint:
    # PHPStan, ESLint, Prettier

  unit-tests:
    needs: lint
    parallel:
      - php-unit    # PHPUnit @unit
      - ts-unit     # Vitest

  integration-tests:
    needs: unit-tests
    parallel:
      - php-integration  # PHPUnit @integration
      - behat-acceptance # Behat features
      - playwright-component

  e2e-tests:
    needs: integration-tests
    # Playwright E2E (serial, not parallel)
```

### Test Failure Handling

1. **Unit test fails**: Block merge, must fix
2. **Integration test fails**: Block merge, investigate
3. **E2E test fails**:
   - First failure: Retry once
   - Second failure: Block merge
   - If flaky: Quarantine with `@flaky` tag

---

## Metrics & Reporting

### Coverage Targets

| Layer | PHP Coverage | TS Coverage |
|-------|-------------|-------------|
| Domain | 95% | N/A |
| Application | 90% | N/A |
| Infrastructure | 80% | N/A |
| Components | N/A | 85% |
| Pages | N/A | 75% |
| Overall | 85% | 80% |

### Reports Generated

- `coverage/php/` - PHPUnit HTML coverage
- `coverage/ts/` - Vitest coverage
- `reports/behat/` - Behat HTML report
- `reports/playwright/` - Playwright trace & screenshots

---

## Getting Started

### Phase 0 Checklist

1. [ ] Install Behat and configure `behat.yml`
2. [ ] Create FeatureContext with base step definitions
3. [ ] Configure Vitest properly (`vitest.config.ts`)
4. [ ] Install Playwright and configure
5. [ ] Install Cucumber.js for Playwright
6. [ ] Create mock factories for common entities
7. [ ] Create test fixtures directory structure
8. [ ] Write first smoke test feature file
9. [ ] Verify CI pipeline runs all test types
10. [ ] Document local test execution commands

### Running Tests Locally

```bash
# PHP Tests
composer test              # All PHP tests
composer test:unit         # Only unit tests
composer test:integration  # Only integration tests
composer test:acceptance   # Only Behat tests
composer test:coverage     # With coverage report

# Frontend Tests
npm test                   # All frontend tests
npm run test:unit          # Vitest unit tests
npm run test:component     # Playwright component tests
npm run test:e2e           # Playwright E2E tests
npm run test:coverage      # With coverage report
```

---

## Appendix A: User Stories Backlog

### Authentication (US-001 to US-005)
- US-001: User Login
- US-002: User Logout
- US-003: Password Reset
- US-004: Two-Factor Authentication
- US-005: Session Management

### Torrents (US-010 to US-020)
- US-010: Browse Torrents
- US-011: Search Torrents
- US-012: Download Torrent
- US-013: Upload Torrent
- US-014: View Torrent Details
- US-015: Bookmark Torrent
- US-016: Report Torrent
- US-017: Request Reseed
- US-018: Freeleech Token Usage
- US-019: Torrent Comments
- US-020: Torrent Ratings

### User Profile (US-030 to US-040)
- US-030: View Profile
- US-031: Edit Profile
- US-032: Change Password
- US-033: Upload Avatar
- US-034: Privacy Settings
- US-035: Notification Preferences
- US-036: View Download History
- US-037: View Upload History
- US-038: Invite Management
- US-039: Bonus Points
- US-040: User Classes/Ranks

### Forums (US-050 to US-060)
- US-050: Browse Forums
- US-051: View Thread
- US-052: Create Thread
- US-053: Reply to Thread
- US-054: Edit Post
- US-055: Delete Post
- US-056: Subscribe to Thread
- US-057: Report Post
- US-058: Search Forums
- US-059: Forum Moderation
- US-060: Pinned/Locked Threads

---

## Appendix B: References

- [Behat Documentation](https://docs.behat.org/)
- [PHPUnit Documentation](https://docs.phpunit.de/)
- [Playwright Documentation](https://playwright.dev/)
- [Cucumber.js Documentation](https://cucumber.io/docs/cucumber/)
- [Martin Fowler: Test Pyramid](https://martinfowler.com/articles/practical-test-pyramid.html)
- [BDD Best Practices](https://cucumber.io/docs/bdd/)
