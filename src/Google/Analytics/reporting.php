<?php
namespace Google\Analytics;

/**
 * A wrapper around the Google Analytics API client which handles formatting
 * request input as well as formatting response data into digestable formats.
 */
class Reporting {

    /**
     * The Google Analytics API client that makes actual calls.
     * @var \Google\Analytics\Client
     */
    public $client;

    /**
     * Default constructor which takes the Google Analytics client as a param.
     *
     * @access  public
     * @param   \Google\Analytics\Client    $client
     * @return  void
     */
    public function __construct(\Google\Analytics\Client $client)
    {
        $this->client = $client;
    }

    /**
     * A wrapper for issuing an API request via the client which sanely
     * validates some inbound parameters.
     *
     * @access  public
     * @param   string  $type
     * @param   string  $startDate
     * @param   string  $endDate
     * @param   string  $groupBy
     * @param   string  $sortBy
     * @return  mixed
     */
    public function generate(
        $type,
        $startDate = NULL,
        $endDate = NULL,
        $groupBy = NULL,
        $sortBy = NULL
    )
    {
        if (!$this->isValidType($type)) {
            throw new Exception('Invalid analytics request type.');
        }

        // default start date to the current date
        if (empty($startDate)) {
            $startDate = date('Y-m-d', strtotime('-7 days'));
        }

        // default end date to 7 days ago
        if (empty($endDate)) {
            $endDate = date('Y-m-d');
        }

        // check for group by / order by
        if (empty($groupBy)) {
            $groupBy = 'date';
        }

        if (empty($sortBy)) {
            $sortBy = 'asc';
        }

        if (!$this->isValidGroupBy($groupBy)) {
            throw new Exception('Invalid group by string entered.');
        }

        if (!$this->isValidSortBy($sortBy)) {
            throw new Exception('Invalid sort by string entered.');
        }

        // update the client filters on the fly
        $this->updateClientFilters($startDate, $endDate, $groupBy, $sortBy);

        // wrap the API request and parse the response
        return $this->request($type);
    }

    /**
     * Right before we issue a client request, we want to update all of the filters.
     *
     * @access  public
     * @param   string  $startDate
     * @param   string  $endDate
     * @param   string  $groupBy
     * @param   string  $orderBy
     * @return  void
     */
    public function updateClientFilters($startDate, $endDate, $groupBy, $sortBy)
    {
        $this->client
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setGroupBy($groupBy)
            ->setSortBy($sortBy);
    }

    /**
     * Given a particular request type, format the response accordingly.
     *
     * @access  public
     * @param   string  $type
     * @return  bool
     */
    public function request($type)
    {
        switch ($type) {
            case 'audience':
                $report = $this->client->getAudienceStatistics();
                $report = $this->format_audience($report);
                break;

            case 'visits-country':
                $report = $this->client->getVisitsByCountries();
                // TODO: formatter for visits by country
                break;

            case 'visits-city':
                $report = $this->client->getVisitsByCities();
                // TODO: formatter for visits by city
                break;

            case 'visits-language':
                $report = $this->client->getVisitsByLanguages();
                // TODO: formatter for visits by language
                break;

            case 'visits-browser':
                $report = $this->client->getVisitsBySystemBrowsers();
                // TODO: formatter for visits by browser
                break;

            case 'visits-os':
                $report = $this->client->getVisitsBySystemOs();
                // TODO: formatter for visits by operating system
                break;

            case 'visits-resolution':
                $report = $this->client->getVisitsBySystemResolutions();
                // TODO: formatter for visits by screen resolution
                break;

            case 'visits-mobile-os':
                $report = $this->client->getVisitsByMobileOs();
                // TODO: formatter for visits by mobile operating system
                break;

            case 'visits-mobile-resolution':
                $report = $this->client->getVisitsByMobileResolutions();
                // TODO: formatter for visits by mobile screen resolution
                break;

            case 'traffic-sources':
                $report = $this->client->getTrafficSources();
                $report = $this->format_traffic_sources($report);
                break;

            case 'referrers':
                $report = $this->client->getReferrers();
                $report = $this->format_referrers($report);
                break;

            case 'keywords':
               $report = $this->client->getKeywords();
               $report = $this->format_keywords($report);
               break;

            case 'adwords':
                $report = $this->client->getAdwords();
                $report = $this->format_adwords($report);
                break;

            case 'pagespeed':
                $report = $this->client->getPageSpeed();
                $report = $this->format_pagespeed($report);
                break;

        }

        // return the formatted report
        return $report;
    }

    /**
     * Check if the request type is valid.
     *
     * @access  public
     * @param   string  $type
     * @return  bool
     */
    public function isValidType($type)
    {
        return in_array(
            $type,
            array(
                'audience', 'visits-country', 'visits-city', 'visits-language',
                'visits-browser', 'visits-os', 'visits-resolution', 'visits-mobile-os',
                'visits-mobile-resolution', 'traffic-sources', 'referrers',
                'keywords', 'adwords', 'pagespeed'
            )
        );
    }

    /**
     * Check if the request group by statement is valid.
     *
     * @access  public
     * @param   string  $groupBy
     * @return  bool
     */
    public function isValidGroupBy($groupBy)
    {
        return in_array(
            $groupBy,
            array(
                'dateHour', 'date', 'month', 'week', 'year', 'month', 'yearWeek',
                'yearMonth', 'dayOfWeekName'
            )
        );
    }

    /**
     * Check if the request sort by statement is valid.
     *
     * @access  public
     * @param   string  $type
     * @return  bool
     */
    public function isValidSortBy($sortBy)
    {
        return in_array($sortBy, array('asc', 'desc'));
    }

    /**
     * Format our audience data.
     *
     * @access  public
     * @param   array   $report
     * @return  mixed
     */
    public function format_audience($report)
    {
        return $report;
    }

    /**
     * Format traffic sources. Below is an example of a report entry.
     *
     * @access  public
     * @param   array   $report
     * @return  mixed
     */
    public function format_traffic_sources($report)
    {
        return $this->format_simple($report, 'ga:medium=');
    }

    /**
     * Format referrers.
     *
     * @access  public
     * @param   array   $report
     * @return  mixed
     */
    public function format_referrers($report)
    {
        return $this->format_simple($report, 'ga:source=');
    }

    /**
     * Format keywords.
     *
     * @access  public
     * @param   array   $report
     * @return  mixed
     */
    public function format_keywords($report)
    {
        return $this->format_simple($report, 'ga:keyword=');
    }

    /**
     * Format adwords.
     *
     * @access  public
     * @param   array   $report
     * @return  mixed
     */
    public function format_adwords($report)
    {
        return $report;
    }

    /**
     * Format pagespeed.
     *
     * @access  public
     * @param   array   $report
     * @return  mixed
     */
    public function format_pagespeed($report)
    {
        return $this->format_multiple_metrics($report);
    }

    /**
     * A simple formatter which takes in a report and a specific data field
     * which allows us to fix dates and mediums.
     *
     * @access  public
     * @param   array   $report
     * @param   string  $dataField
     * @return  mixed
     */
    public function format_simple($report, $dataField)
    {
        $dates = array();
        $metrics = array();
        $map = array();

        // find the aggregate total
        $total = $report['dxp:aggregates']['dxp:metric']['@attributes']['value'];

        // if we have no data
        if (empty($report['entry'])) {
            return array();
        }

        // iterate over each piece of data
        foreach ($report['entry'] as $entry) {
            // parse out dimension data
            list($date, $metric) = explode(' | ', $entry['title']['@content']);

            // fix date and medium
            list($dateCol, $date) = explode('=', $date);
            $date = $this->format_date($dateCol, $date);
            $metric = str_replace($dataField, '', $metric);

            // keep track of all dates
            if (!isset($dates[$date])) {
                $dates[$date] = $date;
            }

            // keep track of all sources
            if (!isset($metrics[$metric])) {
                $metrics[$metric] = $metric;
            }

            // store the value in a map
            if (!isset($map[$date])) {
                $map[$date] = array();
            }
            $map[$date][$metric] = $entry['dxp:metric']['@attributes']['value'];
        }

        // create return data from known dates, dimensions, and global map
        $headings = array_merge(array('Date'), array_values($metrics));
        $return = array($headings);

        foreach ($dates as $date) {
            $row = array($date);
            foreach ($metrics as $metric) {
                $row[] = isset($map[$date][$metric]) ? (int) $map[$date][$metric] : 0;
            }
            $return[] = $row;
        }

        return $return;
    }

    /**
     * In some cases we have multiple metrics in the result set that we need to
     * format. This takes some extra work on our end to get them in a digestable
     * format.
     *
     * @access  public
     * @param   array   $report
     * @return  mixed
     */
    public function format_multiple_metrics($report)
    {
        $dates = array();
        $metrics = array();
        $map = array();

        // find the aggregate total
        // $total = $report['dxp:aggregates']['dxp:metric']['@attributes']['value'];

        // if we have no data
        if (empty($report['entry'])) {
            return array();
        }

        // iterate over each piece of data
        foreach ($report['entry'] as $entry) {
            // get the date dimension
            $date = $this->format_date($entry['dxp:dimension']['@attributes']['name'], $entry['dxp:dimension']['@attributes']['value']);

            if (!isset($dates[$date])) {
                $dates[$date] = $date;
            }

            if (!isset($map[$date])) {
                $map[$date] = array();
            }

            // iterate over the crazy list of metrics
            foreach ($entry['dxp:metric'] as $entryMetric) {
                $metric = $entryMetric['@attributes']['name'];

                if (!isset($metrics[$metric])) {
                    $metrics[$metric] = $metric;
                }

                if (!isset($map[$date][$metric])) {
                    $map[$date][$metric] = $entryMetric['@attributes']['value'];
                }
            }
        }

        // create return data from known dates, dimensions, and global map
        $headings = array_merge(array('Date'), array_values($metrics));
        $return = array($headings);

        foreach ($dates as $date) {
            $row = array($date);
            foreach ($metrics as $metric) {
                $row[] = isset($map[$date][$metric]) ? (int) $map[$date][$metric] : 0;
            }
            $return[] = $row;
        }

        return $return;
    }

    /**
     * When we want to return tabular data, especially when there's multiple metrics,
     * we want the date to show up on the top and the headings to show up down the
     * left column.
     *
     *     date     | 10 | 11 | 12
     *     metric   | a  | b  | c
     *     metric 2 | d  | e  | f
     */
    public function format_table($report)
    {
        $dates = array();
        $metrics = array();
        $map = array();

        // find the aggregate total
        // $total = $report['dxp:aggregates']['dxp:metric']['@attributes']['value'];

        // if we have no data
        if (empty($report['entry'])) {
            return array();
        }

        // iterate over each piece of data
        foreach ($report['entry'] as $entry) {
            // get the date dimension
            $date = $this->format_date($entry['dxp:dimension']['@attributes']['name'], $entry['dxp:dimension']['@attributes']['value']);

            if (!isset($dates[$date])) {
                $dates[$date] = $date;
            }

            // iterate over the crazy list of metrics
            foreach ($entry['dxp:metric'] as $entryMetric) {
                $metric = $entryMetric['@attributes']['name'];

                if (!isset($metrics[$metric])) {
                    $metrics[$metric] = $metric;
                }

                if (!isset($map[$metric])) {
                    $map[$metric] = array();
                }

                if (!isset($map[$metric][$date])) {
                    $map[$metric][$date] = $entryMetric['@attributes']['value'];
                }
            }
        }

        // create return data from known dates, dimensions, and global map
        $headings = array_merge(array('Date'), array_values($dates));
        $return = array($headings);

        foreach ($metrics as $metric) {
            $row = array($metric);
            foreach ($dates as $date) {
                $row[] = isset($map[$metric][$date]) ? (int) $map[$metric][$date] : 0;
            }
            $return[] = $row;
        }

        return $return;
    }

    /**
     * Check if a given field name is a Google date field.
     *
     * @access  public
     * @param   string  $name
     * @return  bool
     */
    public function is_date($name)
    {
        $map = array(
            'ga:date'           => '1',
            'ga:year'           => '1',
            'ga:month'          => '1',
            'ga:week'           => '1',
            'ga:day'            => '1',
            'ga:hour'           => '1',
            'ga:yearMonth'      => '1',
            'ga:yearWeek'       => '1',
            'ga:dateHour'       => '1',
            'ga:nthMonth'       => '1',
            'ga:nthWeek'        => '1',
            'ga:nthDay'         => '1',
            'ga:isoWeek'        => '1',
            'ga:dayOfWeek'      => '1',
            'ga:dayOfWeekName'  => '1'
        );

        return isset($map[$name]);
    }

    /**
     * Create a prettier date where applicable.
     *
     * @access  public
     * @param   string  $name
     * @param   string  $value
     * @return  string
     */
    public function format_date($name, $value)
    {
        $months = array(
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        );

        switch ($name) {
            case 'ga:date':
                $value = substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2);
                break;

            case 'ga:month':
                $month = (int) $value;
                $value = $months[$month];
                break;

            case 'ga:yearMonth':
                $split = explode(' ', $value);
                $month = (int) $split[1];
                $value = $months[$month] . ' ' . $split[0];
                break;

            case 'ga:dayOfWeek':
                $days = array(
                    0 => 'Sun',
                    1 => 'Mon',
                    2 => 'Tue',
                    3 => 'Wed',
                    4 => 'Thu',
                    5 => 'Fri',
                    6 => 'Sat'
                );
                $value = $days[$value];
                break;

            case 'ga:dayOfWeekName':
                $days = array(
                    'Sunday'    => 'Sun',
                    'Monday'    => 'Mon',
                    'Tuesday'   => 'Tue',
                    'Wednesday' => 'Wed',
                    'Thursday'  => 'Thu',
                    'Friday'    => 'Fri',
                    'Saturday'  => 'Sat'
                );
                $value = $days[$value];
                break;

            default:
                break;
        }

        return $value;
    }

    /**
     * Switch a Google date field for a display name.
     *
     * @access  public
     * @param   string  $name
     * @return  string
     */
    public function display_date($name)
    {
        $map = array(
            'ga:date'           => 'Date',
            'ga:year'           => 'Year',
            'ga:month'          => 'Month',
            'ga:week'           => 'Week',
            'ga:day'            => 'Day of Month',
            'ga:hour'           => 'Hour of Day',
            'ga:yearMonth'      => 'Month and Year',
            'ga:yearWeek'       => 'Week and Year',
            'ga:dateHour'       => 'Date and Time',
            'ga:nthMonth'       => 'Nth Month',
            'ga:nthWeek'        => 'Nth Week',
            'ga:nthDay'         => 'Ntn Day',
            'ga:isoWeek'        => 'Week',
            'ga:dayOfWeek'      => 'Day of Week',
            'ga:dayOfWeekName'  => 'Day of Week',
        );

        return $map[$name];
    }

}
