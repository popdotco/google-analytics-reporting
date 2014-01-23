<?php
namespace Google\Analytics;

class Client {

    public $username;
    public $password;

    public function __construct($username, $password, $analytics)
    {
        $this->setUsername($username);
        $this->setPassword($password);
        
        $this->generateAuthToken();
    }
    
    public function setUsername($username) 
    {
        $this->username = $username;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
    }
    
    public function generateAuthToken()
    {

        // generate auth token
    	$tz = @date_default_timezone_get();
    	date_default_timezone_set('UTC');

        // TODO: get auth token
    	$auth_token = GAnalytics::auth($this->getUsername(), $this->getPassword());
    }
    
    public function getUsername()
    {
        return $this->username;
    }
    
    public function getPassword()
    {
        return $this->password;
    }

    public function request($type)
    {
        if (!$this->isValidType($type)) {
            throw new Exception('Invalid type.');
        }
        
        $end_date = date('Y-m-d');
        if (!empty($_POST['end_date'])) {
            $end_date = $_POST['end_date'];
        }
    
        $start_date = '2013-03-13';
        if (!empty($_POST['start_date'])) {
            $start_date = $_POST['start_date'];
        }
    
        // check for group by / order by
        $groupBy = 'date';
        if (!empty($_POST['group'])) {
            $groupBy = $_POST['group'];
        }
    
        if (!in_array($groupBy, array('dateHour', 'date', 'month', 'week', 'year', 'month', 'yearWeek', 'yearMonth', 'dayOfWeekName'))) {
            die(json_encode(array('code' => 500, 'msg' => 'Invalid group by.')));
        }
        
        $sortBy = 'asc';
        if (!empty($_POST['sortorder'])) {
            $sortBy = $_POST['sortorder'];
        }
    
        if (!in_array($sortBy, array('asc', 'desc'))) {
            die(json_encode(array('code' => 500, 'msg' => 'Invalid sort order.')));
        }
        
        return $this->respondByType($type);
    }
    
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
        )
    }

    public function respondByType($type)
    {
        switch ($type) {
            case 'audience':
                $report = GAnalytics::getAudienceStatistics($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                $report = format_audience($report);
                break;
    
            case 'visits-country':
                $report = GAnalytics::getVisitsByCountries($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                break;
    
            case 'visits-city':
                $report = GAnalytics::getVisitsByCities($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                break;
    
            case 'visits-language':
                $report = GAnalytics::getVisitsByLanguages($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                break;
    
            case 'visits-browser':
                $report = GAnalytics::getVisitsBySystemBrowsers($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                break;
    
            case 'visits-os':
                $report = GAnalytics::getVisitsBySystemOs($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                break;
    
            case 'visits-resolution':
                $report = GAnalytics::getVisitsBySystemResolutions($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                break;
    
            case 'visits-mobile-os':
                $report = GAnalytics::getVisitsByMobileOs($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                break;
    
            case 'visits-mobile-resolution':
                $report = GAnalytics::getVisitsByMobileResolutions($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                break;
    
            case 'traffic-sources':
                $report = GAnalytics::getTrafficSources($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                $report = format_traffic_sources($report);
                break;
    
            case 'referrers':
                $report = GAnalytics::getReferrers($auth_token, $start_date, $end_date, $groupBy);
                $report = format_referrers($report);
                break;
    
            case 'keywords':
               $report = GAnalytics::getKeywords($auth_token, $start_date, $end_date, $groupBy, $sortBy);
               $report = format_keywords($report);
               break;
    
            case 'adwords':
                $report = GAnalytics::getAdwords($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                $report = format_adwords($report);
                break;
    
            case 'pagespeed':
                $report = GAnalytics::getPageSpeed($auth_token, $start_date, $end_date, $groupBy, $sortBy);
                $report = format_pagespeed($report);
                break;

        }

        // reset timezone
        date_default_timezone_set($tz);

        // handle response as JSON
        if (empty($report)) {
            $report = json_encode(array('data' => array()));
        } else {
            $report = json_encode(array('data' => $report));
        }
    
        if (!empty($_POST['callback'])) {
            echo $_POST['callback'] . '(' . $report . ')';
        } else {
            echo $report;
        }
        die;
    }

    /**
     * Format our audience data.
     */
    public function format_audience($report) 
    {
        return $report;
    }

    /**
     * Format traffic sources. Below is an example of a report entry.
     *
     */
    public function format_traffic_sources($report) 
    {
        return format_simple($report, 'ga:medium=');
    }

    /**
     * Format referrers.
     */
    public function format_referrers($report) 
    {
        return format_simple($report, 'ga:source=');
    }

    /**
     * Format keywords.
     */
    public function format_keywords($report) 
    {
        return format_simple($report, 'ga:keyword=');
    }

    /**
     * Format adwords.
     */
    public function format_adwords($report) 
    {
        return $report;
    }

    /**
     * Format pagespeed.
     */
    public function format_pagespeed($report) 
    {
        return format_multiple_metrics($report);
    }

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
            $date = format_date($dateCol, $date);
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
     * In some cases we have multiple metrics in the resultset that we need to
     * format. This takes some extra work on our end.
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
            $date = format_date($entry['dxp:dimension']['@attributes']['name'], $entry['dxp:dimension']['@attributes']['value']);
    
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
            $date = format_date($entry['dxp:dimension']['@attributes']['name'], $entry['dxp:dimension']['@attributes']['value']);
    
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
     */
    public function is_date($name) {
        $map = array(
            'ga:date' => '1',
            'ga:year' => '1',
            'ga:month' => '1',
            'ga:week' => '1',
            'ga:day' => '1',
            'ga:hour' => '1',
            'ga:yearMonth' => '1',
            'ga:yearWeek' => '1',
            'ga:dateHour' => '1',
            'ga:nthMonth' => '1',
            'ga:nthWeek' => '1',
            'ga:nthDay' => '1',
            'ga:isoWeek' => '1',
            'ga:dayOfWeek' => '1',
            'ga:dayOfWeekName' => '1',
        );
    
        return isset($map[$name]);
    }

    /**
     * Create a prettier date where applicable.
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
