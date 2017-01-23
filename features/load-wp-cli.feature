Feature: Test that WP-CLI loads.

  Scenario: Scaffold a Movefile
    Given a WP install

    When I run `wp help scaffold movefile`
    Then the return code should be 0

    When I run `wp scaffold movefile`
    Then the return code should be 0
    And the Movefile.yml file should exist
    And STDOUT should contain:
      """
      Success:
      """

  Scenario: Overwrite Movefile by prompting yes
    Given a WP install
    And a Movefile file:
      """
      Hello
      """
    And a session file:
      """
      y
      """

    When I run `wp scaffold movefile < session`
    Then the return code should be 0
    And the Movefile.yml file should exist
    And the Movefile.yml file should contain:
      """
      local:
      """
    And STDOUT should contain:
      """
      Success:
      """

  Scenario: Don't overwrite Movefile
    Given a WP install
    And a Movefile.yml file:
      """
      Hello
      """
    And a session file:
      """
      n
      """

    When I run `wp scaffold movefile < session`
    Then the return code should be 0
    And the Movefile.yml file should exist
    And the Movefile.yml file should contain:
      """
      Hello
      """
    And STDOUT should contain:
      """
      Success:
      """

  Scenario: Force overwrite Movefile
    Given a WP install
    And a Movefile.yml file:
      """
      Hello
      """

    When I run `wp scaffold movefile --force`
    Then the return code should be 0
    And the Movefile.yml file should exist
    And the Movefile.yml file should contain:
      """
      local:
      """
