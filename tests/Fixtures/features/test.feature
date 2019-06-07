Feature: Test

  Scenario: Foo Bar Output
    Given I say foo
    Then console output should contain "Foo Bar"

  Scenario: Hello World Output
    Given I say hello
    Then console output should contain "Hello World"

  @local
  Scenario: Test local coverage
    Given I send a "GET" request to "/"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node foo should exist
    And the JSON node hello should exist
    And the JSON node blacklist should exist

  @remote
  Scenario: Test remote coverage
    Given I send a "GET" request to "/remote.php"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node remote should exist
    And the response should contain "hello from remote"
