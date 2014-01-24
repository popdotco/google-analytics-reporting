A library intended to help pull out the pertinent data you want, in an easily digestable format, from the Google Analytics API.

At POP, we have our own administrative tools including a display of some key Google Analytics metrics for easy browsing without logging into Google Analytics directly. Since we spent the time and effort figuring out how to parse and represent the data for many common Google Analytics endpoints, we figured we'd share our trials and tribulations with you.

This repository is a combination of a Google Analytics API client as well as an all-purpose request handler for easily generating requests and formatted responses to the Google Analytics API. We did all of the grunt work so you don't have to, including:

 * Search by date range
 * Group by a time based field: `dateHour`, `date`, `month`, `week`, `year`, `month`, `yearWeek`, `yearMonth`, `dayOfWeekName`
 * Changing the result sort order: `asc`, `desc`

## Usage ##

```php
<?php
$authToken = NULL;
$username = 'my.analytics.email@example.co';
$password = 'my.analytics.password';
$profileId = 'my-api-console-project-id';

// load the API client
$client = new Google\Analytics\Client($authToken, $username, $password);

// we also want to set the client profile id
// which is the unique ID of your Google API Console project
$client->setProfileId($profileId);

// load the reporting class which wraps the API client
$reporting = new Google\Analytics\Reporting($client);

// generate a report given these filters
$reportType = 'referrers';
$startDate = '2014-01-01';
$endDate = '2014-01-07';
$groupBy = 'date';
$orderBy = 'asc';

$results = $reporting->generate($reportType, $startDate, $endDate, $groupBy, $orderBy);
var_dump($results); 

```
 
## Endpoints ##

TODO. Some of these are experimental and data is returning incorrectly or in a non-parsed format.

#### Audience ####

#### Visits by Country ####

#### Visits by City ####

#### Visits by Language ####

#### Visits by Browser ####

#### Visits by Operating System ####

#### Visits by Screen Resolution ####

#### Visits by Mobile Operating System ####

#### Traffic Sources ####

#### Referrers ####

#### Keywords ####

#### Adwords ####

#### Page Speed (Site Performance) ####
