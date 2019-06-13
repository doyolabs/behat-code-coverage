Feature: Remote Server Coverage

  @remote
  Scenario: Access with invalid action
    Given I send a GET request to "/coverage.php?action=foo"
    Then the response status code should be 404
    And the response should be in JSON
    And the JSON node message should exist
    And the response should contain "The page you requested is not exists"

  @remote
  Scenario: Successfully create new coverage session
    Given I add "Accept" header equal to "application/json"
    And I initialize new remote session foo with:
    """
    {
      "filterOptions": {
        "whitelistedFiles": []
      },
      "codeCoverageOptions": {}
    }
    """
    Then the response status code should be 202
    And the response should be in JSON
    And the response should contain "session: foo initialized"

  @remote
  Scenario: Read uninitialized coverage session
    Given I read coverage session "bar"
    Then the response status code should be 404
    And the response should contain "Session bar is not initialized"

  @remote
  Scenario: Read initialized coverage session
    Given I initialize new remote session foo with:
    """
    {
      "filterOptions": {
        "whitelistedFiles": []
      },
      "codeCoverageOptions": {}
    }
    """
    When I read coverage session foo
    Then the response status code should be 200
    And the content should be serialized
    And the content should be a session
