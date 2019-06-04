Feature: Coverage

  Scenario: Run code coverage
    Given I run behat
    Then console output should contain "passed"
    And file "build/cov/behat.cov" should exist
    And file "build/clover.xml" should exist
    And directory "build/html" should exist
    And directory "build/xml" should exist
