@authentication @smoke @critical
Feature: User Login
  As a registered user
  I want to log in with my credentials
  So that I can access my account and download torrents

  Background:
    Given the current time is "2024-01-15 10:00:00"

  # ========================================
  # Happy Path Scenarios
  # ========================================

  @happy-path
  Scenario: Successfully log in with valid credentials
    Given I am a registered user
    When I submit valid login credentials
    Then I should be authenticated
    And a session should be created
    And the session should expire in 30 days
    And my login attempt should be logged

  @happy-path
  Scenario: Login with specific email
    Given I am a registered user with email "test@example.com"
    When I submit login credentials with email "test@example.com" and password "TestPassword123!"
    Then I should be authenticated

  # ========================================
  # Error Scenarios
  # ========================================

  @error-handling
  Scenario: Fail to log in with wrong password
    Given I am a registered user
    When I submit invalid login credentials
    Then I should not be authenticated
    And I should see authentication error "Invalid credentials"
    And my login attempt should be logged

  @error-handling
  Scenario: Fail to log in with non-existent email
    Given I am a guest user
    When I submit login credentials with email "nonexistent@example.com" and password "AnyPassword123!"
    Then I should not be authenticated
    And I should see authentication error "Invalid credentials"

  @error-handling
  Scenario: Fail to log in with disabled account
    Given I am a registered user
    And my account is disabled
    When I submit valid login credentials
    Then I should not be authenticated
    And I should see authentication error "Account is disabled"

  @error-handling
  Scenario: Fail to log in with banned account
    Given I am a registered user
    And my account is banned
    When I submit valid login credentials
    Then I should not be authenticated
    And I should see authentication error "Account is disabled"

  # ========================================
  # Rate Limiting Scenarios
  # ========================================

  @security @rate-limiting
  Scenario: Rate limited after too many failed attempts
    Given I am a registered user
    And I have exceeded the login rate limit
    When I submit valid login credentials
    Then I should be rate limited
    And I should see authentication error "Too many login attempts"

  @security @rate-limiting
  Scenario: Rate limit resets after waiting period
    Given I am a registered user
    And I have exceeded the login rate limit
    And 60 seconds have passed
    When I submit valid login credentials
    Then I should be authenticated

  @security @rate-limiting
  Scenario Outline: Gradual rate limiting on failed attempts
    Given I am a registered user
    When I submit login with wrong password <attempts> times
    Then <result>

    Examples:
      | attempts | result                           |
      | 3        | I should not be authenticated    |
      | 5        | I should be rate limited         |

  # ========================================
  # Session Management Scenarios
  # ========================================

  @session
  Scenario: Access protected resource when logged in
    Given I am logged in
    When I access a protected resource
    Then the operation should succeed

  @session
  Scenario: Redirect to login when not authenticated
    Given I am a guest user
    When I access a protected resource
    Then I should be redirected to login

  @session
  Scenario: Session expired requires re-login
    Given I am logged in
    And my session has expired
    When I access a protected resource
    Then I should be redirected to login

  # ========================================
  # Role-Based Access Scenarios
  # ========================================

  @roles
  Scenario Outline: Login with different user roles
    Given I am logged in as a "<role>"
    When I access a protected resource
    Then the operation should succeed

    Examples:
      | role           |
      | user           |
      | member         |
      | power_user     |
      | moderator      |
      | admin          |
