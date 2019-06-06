Feature: Coverage

  Scenario: Run code coverage
    Given I run behat with "--coverage"
    Then console output should contain "passed"
    And directory "build/html" should exist
    And file "build/clover.xml" should exist
    And file "build/cov/behat.cov" should exist
    And file "build/crap4j.xml" should exist
    And console output should contain "behat coverage reports process started"
    And console output should contain "behat coverage reports process completed"

  @remote
  Scenario: Run with remote coverage
    Given I run behat with coverage and profile "remote"
    Then console output should contain "6 steps (6 passed)"
    And file "src/Foo.php" line 20 should be covered
    And file "src/Hello.php" line 20 should be covered
    And file "src/blacklist/blacklist.php" should not be covered
