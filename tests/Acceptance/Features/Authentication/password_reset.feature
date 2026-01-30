@authentication @security
Feature: Password Reset
  As a user who forgot their password
  I want to reset my password via email
  So that I can regain access to my account

  Background:
    Given the current time is "2024-01-15 10:00:00"

  @happy-path
  Scenario: Request password reset for existing account
    Given I am a registered user with email "user@example.com"
    When I request a password reset for "user@example.com"
    Then the operation should succeed
    And a password reset email should be sent

  @security @enumeration-prevention
  Scenario: Request password reset for non-existent email (no enumeration)
    When I request a password reset for "nonexistent@example.com"
    Then the operation should succeed
    # Note: Response is identical to prevent email enumeration attacks
    # Internally, no email is sent for non-existent accounts

  @security
  Scenario: Password reset for disabled account
    Given I am a registered user with email "disabled@example.com"
    And my account is disabled
    When I request a password reset for "disabled@example.com"
    Then the operation should succeed
    # Disabled accounts should still receive reset emails
    # Account status is verified after password reset
