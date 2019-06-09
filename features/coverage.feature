Feature: Coverage

  Scenario: Run code coverage
    Given I run behat with "--coverage"
    Then console output should contain "passed"
    And directory "build/html" should exist
    And file "build/clover.xml" should exist
    And file "build/cov/behat.cov" should exist
    And file "build/crap4j.xml" should exist
    And console output should contain "generating code coverage report"
    And console output should contain "behat code coverage generated"

  @remote
  Scenario: Run with local coverage
    Given I run behat with coverage and profile "local"
    Then console output should contain "6 steps (6 passed)"
    And console output should contain "generating code coverage report"
    And console output should contain "behat code coverage generated"
    And file "src/Foo.php" line 20 should be covered
    And file "src/Hello.php" line 20 should be covered
    And file "src/blacklist/blacklist.php" should not be covered

  @remote
  Scenario: Run with remote coverage
    Given I run behat with coverage and profile "remote"
    Then console output should contain "5 steps (5 passed)"
    And console output should contain "generating code coverage report"
    And console output should contain "behat code coverage generated"
    And file "src/remote/Remote.php" line 20 should be covered
