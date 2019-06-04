Feature: Test

  Scenario: Foo Bar Output
    Given I say foo
    Then console output should contain "Foo Bar"

  Scenario: Hello World Output
    Given I say hello
    Then console output should contain "Hello World"
