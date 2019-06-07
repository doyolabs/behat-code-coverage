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
    And I send a POST request to "/coverage.php?action=init&session=foo" with body:
    """
    {
      "filter": {},
      "codeCoverageOptions": {}
    }
    """
    Then the response status code should be 202
    And the response should be in JSON
    And the response should contain "session: foo initialized"
