<?php

/**
 * To create new methods, look into the available dimensions & metrics:
 * https://developers.google.com/analytics/devguides/reporting/core/dimsmets
 *
 * Usage of these dimension and metrics within queries is as follows:
 * https://developers.google.com/analytics/devguides/reporting/core/v3/reference
 */

class Analytics {

	const loginUrl = 'https://www.google.com/accounts/ClientLogin';
	const accountUrl = 'https://www.google.com/analytics/feeds/accounts/default';
	const reportUrl = 'https://www.google.com/analytics/feeds/data';
	const interfaceName = 'GAPI-1.3';
	const ga_profile_id = '';

    public static $report_type;
    
    /**
     * Default constructor to setup the client library for accessing the Google Analytics API.
     */
    public function __construct()
    {
    
    }

    /**
     * Some general statistics on the audience.
     */
    public static function getAudienceStatistics($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'audience';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visitors,ga:newVisits,ga:percentNewVisits,ga:visits,ga:bounces,ga:pageviews,ga:visitBounceRate,ga:timeOnSite,ga:avgTimeOnSite',
            $groupby,
            $orderby
        );
	}

    /**
     * Get traffic sources.
     *
     * https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
     */
    public static function getTrafficSources($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'traffic-sources';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:medium',
            $orderby
        );
    }

    /**
     * Get site referrers.
     *
     * https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
     */
    public static function getReferrers($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'referrers';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:source',
            $orderby . ',-ga:visits'
        );
    }

    /**
     * Get inbound keyword traffic results.
     *
     * https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
     */
    public static function getKeywords($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'keywords';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:keyword',
            $orderby . ',-ga:visits'
        );
	}

    /**
     * Get the page speed.
     */
    public static function getPageSpeed($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'pagespeed';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:avgPageLoadTime,ga:avgDomainLookupTime,ga:avgPageDownloadTime,ga:avgServerConnectionTime,ga:avgServerResponseTime,ga:avgDomInteractiveTime,ga:avgDomContentLoadedTime',
            $groupby,
            $orderby
        );
    }

    /**
     * Get some adword performance metrics by month and year.
     *
     * http://www.crescentinteractive.com/custom-adwords-apps-scripts/
     */
    public static function getAdwords($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'adwords';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:adclicks,ga:impressions,ga:cpc,ga:adCost',
            $groupby,
            $orderby
        );
    }

    /**
     * Visits by country.
     */
	public function getVisitsByCountries($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'visits-by-country';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:country',
            $orderby . ',-ga:visits'
        );
	}

    /**
     * Visits by city.
     */
	public function getVisitsByCities($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'visits-by-city';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:city',
            $orderby . ',-ga:visits'
        );
	}

    /**
     * Visits by language.
     */
	public function getVisitsByLanguages($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'visits-by-language';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:language',
            $orderby . ',-ga:visits'
        );
	}

    /**
     * Visits by browser.
     */
	public function getVisitsBySystemBrowsers($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'visits-by-browser';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:browser',
            $orderby . ',-ga:visits'
        );
	}

    /**
     * Visits by OS.
     */
	public function getVisitsBySystemOs($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'visits-by-os';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:operatingSystem',
            $orderby . ',-ga:visits'
        );
	}

    /**
     * Visits by resolution.
     */
	public function getVisitsBySystemResolutions($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'visits-by-resolution';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:screenResolution',
            $orderby . ',-ga:visits',
            'gaid::-11'
        );
	}

    /**
     * Visits by mobile OS.
     */
	public function getVisitsByMobileOs($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'visits-by-mobile';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:operatingSystem',
            $orderby . ',-ga:visits',
            'gaid::-11'
        );
	}

    /**
     * Visits by mobile resolution.
     */
	public function getVisitsByMobileResolutions($auth_token, $start, $end, $groupby = 'daily', $sortby = 'asc', $profile_id = NULL)
    {
        if (empty($profile_id)) {
            $profile_id = GAnalytics::ga_profile_id;
        }

        // figure out if we're grouping by anything specific
        $orderby = static::sortBy($groupby, $sortby);
        $groupby = static::groupBy($groupby);

        static::$report_type = 'visits-by-mobile-resolution';

        return static::report(
            $auth_token,
            $profile_id,
            $start,
            $end,
            'ga:visits',
            $groupby . ',ga:screenResolution',
            $orderby . ',-ga:visits',
            'gaid::-11'
        );
	}

    /**
     ***************************************************************************
     ***************************************************************************
     * system level functions below here that just pertain to auth and request(s).
     ***************************************************************************
     ***************************************************************************
     */

    public static function groupBy($groupby)
    {
        if ($groupby == 'hourly') {
            return 'ga:dateHour';
        } else if ($groupby == 'daily') {
            return 'ga:date';
        } else if ($groupby == 'weekly') {
            return 'ga:yearWeek';
        } else if ($groupby == 'monthly') {
            return 'ga:yearMonth';
        } else if ($groupby == 'yearly') {
            return 'ga:year';
        } else if ($groupby == 'dayofweek') {
            return 'ga:dayOfWeek';
        }

        return 'ga:date';
    }

    public static function sortBy($groupby, $order = 'asc')
    {
        $prefix = strtolower($order) == 'asc' ? '' : '-';

        if ($groupby == 'hourly') {
            return $prefix . 'ga:dateHour';
        } else if ($groupby == 'daily') {
            return $prefix . 'ga:date';
        } else if ($groupby == 'weekly') {
            return $prefix . 'ga:yearWeek';
        } else if ($groupby == 'monthly') {
            return $prefix . 'ga:yearMonth';
        } else if ($groupby == 'yearly') {
            return $prefix . 'ga:year';
        } else if ($groupby == 'dayofweek') {
            return $prefix . 'ga:dayOfWeek';
        }

        return $prefix . 'ga:date';
    }

    /**
     * Handle authentication.
     */
	public static function auth($email, $password)
	{
		$response = GAnalytics::post(
			GAnalytics::loginUrl,
			array(
				'accountType' => 'GOOGLE',
				'Email' => $email,
				'Passwd' => $password,
				'source' => GAnalytics::interfaceName,
				'service' => 'analytics'
			)
		);

		$str = str_replace(array("\n","\r\n"), '&', $response);
		parse_str($str, $auth);

		return $auth['Auth'];
	}

    /**
     * Run the actual report and return data.
     */
	public static function report(
        $auth_token,
        $profile_id,
        $start,
        $end,
        $metrics,
        $dimensions = NULL,
        $sort = NULL,
        $segment = NULL
    )
	{
		$params = array();
		$params['ids'] = 'ga:' . $profile_id;
		$params['start-date'] = $start;
		$params['end-date'] = $end;
		$params['metrics'] = $metrics;

		if (!empty($dimensions)) {
			$params['dimensions'] = $dimensions;
		}

        if (!empty($sort)) {
            $params['sort'] = $sort;
        }

        if (!empty($segment)) {
            $params['segment'] = $segment;
        }

        // max out our max results
        $params['max-results'] = 10000;

        // generate memcache key
        $cache_key = sha1(serialize($params));
        Cache::delete($cache_key);
        $response = Cache::get($cache_key);
        if (!empty($response)) {
            error_log('Returning cached analytics response.');
            return xmlstr_to_array($response);
        }

		$url = self::reportUrl . '?' . http_build_query($params);
		$response = static::post($url, null, $auth_token);

        // handle logging sample response
        static::log_response($response);

        // handle caching the XML response
        Cache::set($cache_key, $response);

        // return as array
		return xmlstr_to_array($response);
	}

    /**
     * Handle post request.
     */
	public static function post($url, $vars = null, $auth_token = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if (is_array($vars)) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		}

		if (!empty($auth_token)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: GoogleLogin auth=' . $auth_token));
		}

		return curl_exec($ch);
	}

    /**
     * If we didn't save a sample of the response, do so.
     */
    public static function log_response($report)
    {
        $file = BASEPATH . '/api/analytics/responses/' . static::$report_type . '.log';
        error_log($file);
        if (file_exists($file)) {
            return;
        }

        file_put_contents($file, $report, LOCK_EX);
    }

}
