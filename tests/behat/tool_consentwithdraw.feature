@tool @tool_consentwithdraw
Feature: Withdraw AI policy consent
  As a user or admin I want to be able to withdraw AI policy consent.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | One      | user1@example.com |
      | user2    | User      | Two      | user2@example.com |
    And the following "system role assigns" exist:
      | user  | role    |
      | user2 | manager |

  Scenario: Admin can access the consent withdraw tool page
    Given I log in as "user2"
    When I navigate to "/admin/tool/consentwithdraw/index.php"
    Then I should see "Withdraw AI Policy Consent"
    And I should see "Search user"

  Scenario: Regular user cannot access the admin page
    Given I log in as "user1"
    When I navigate to "/admin/tool/consentwithdraw/index.php"
    Then I should see "Sorry, but you do not currently have permissions to do this"
