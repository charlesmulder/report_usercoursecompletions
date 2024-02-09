@report_usercoursecompletions
Feature: In a report, admin can see list of users

  # Set some config values so the report contains known data.
  Background:
    Given I log in as "admin"
    And I change the window size to "large"

  @javascript
  Scenario: Display configuration changes report
    When I navigate to "Reports > User Course Completions" in site administration
    Then the following should exist in the "reportbuilder-table" table:
      | User       | Plugin | Setting             | New value | Original value |
      | Admin User | quiz   | initialnumfeedbacks | 5         | 2              |
      | Admin User | folder | maxsizetodownload   | 2048      | 0              |
      | Admin User | core   | defaultcity         | Perth     |                |

