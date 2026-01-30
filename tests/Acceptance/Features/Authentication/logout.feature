@authentication @session
Feature: User Logout
  As a logged-in user
  I want to log out of my account
  So that my session is securely ended

  Background:
    Given the current time is "2024-01-15 10:00:00"

  @happy-path @smoke
  Scenario: Successfully log out
    Given I am logged in
    When I log out
    Then the operation should succeed
    And I should be redirected to login

  @session
  Scenario: Session is invalidated after logout
    Given I am logged in
    When I log out
    And I access a protected resource
    Then I should be redirected to login

  @security
  Scenario: Cannot logout when not logged in
    Given I am a guest user
    When I log out
    Then the operation should succeed
