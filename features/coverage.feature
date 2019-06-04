Feature: Coverage

  Scenario: Run code coverage
    Given I run behat
    Then console output should contain "passed"
    And directory "build/html" should exist
    And file "build/clover.xml" should exist
    And file "build/cov/behat.cov" should exist
    And directory "build/xml" should exist
    And file "build/crap4j.xml" should exist
    And console output should contain "Processing Code Coverage Reports"
