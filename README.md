A library intended to help pull out the pertinent data you want, in an easily digestable format, from the Google Analytics API.

At POP, we have our own administrative tools including a display of some key Google Analytics metrics for easy browsing without logging into Google Analytics directly. Since we spent the time and effort figuring out how to parse and represent the data for many common Google Analytics endpoints, we figured we'd share our trials and tribulations with you.

This repository is a combination of a Google Analytics API client as well as an all-purpose request handler for easily generating requests and formatted responses to the Google Analytics API. We did all of the grunt work so you don't have to, including:

 * Search by date range
 * Group by a time based field: `dateHour`, `date`, `month`, `week`, `year`, `month`, `yearWeek`, `yearMonth`, `dayOfWeekName`
 * Changing the result sort order: `asc`, `desc`
 
## Endpoints ##

Some of these are experimental:

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
