<?php
namespace Google\Analytics;

/**
 * To create new methods, look into the available dimensions & metrics:
 * https://developers.google.com/analytics/devguides/reporting/core/dimsmets
 *
 * Usage of these dimension and metrics within queries is as follows:
 * https://developers.google.com/analytics/devguides/reporting/core/v3/reference
 */

class Client {

	public $loginUrl              = 'https://www.google.com/accounts/ClientLogin';
	public $accountUrl            = 'https://www.google.com/analytics/feeds/accounts/default';
	public $reportUrl             = 'https://www.google.com/analytics/feeds/data';
	public $interfaceName         = 'GAPI-1.3';

    /**
     * Maintains all parameters we may want to pass along with the request for
     * filtering capabilities.
     * @var array
     */
    public $params;

    /**
     * The username and password necessary for generating an auth token.
     * @var string
     */
    public $username;
    public $password;

    /**
     * Default constructor for setting up the Google Analytics client.
     *
     * @access  public
     * @param   string  $token
     * @param   string  $username
     * @param   string  $password
     * @param
     */
    public function __construct($token = NULL, $username = NULL, $password = NULL)
    {
        // quick validation
        if (empty($token) && (empty($username) || empty($password))) {
            throw new Exception('You must supply either an auth token or a username/password.');
        }

        // handle the auth token
        if (!empty($token)) {
            $this->setAuthToken($token);
        } else {
            $this->setUsername($username);
            $this->setPassword($password);

            $this->generateAuthToken();
        }
    }

    /**
     * Handle setting an authorization token for authenticating the request.
     *
     * @access  public
     * @param   string  $authToken
     * @return  $this
     */
    public function setAuthToken($authToken)
    {
        if (!empty($authToken)) {
            $this->params['auth_token'] = $authToken;
        }

        return $this;
    }

    /**
     * Handle setting a profile id.
     *
     * @access  public
     * @param   string  $profileId
     * @return  $this
     */
    public function setProfileId($profileId)
    {
        if (!empty($profileId)) {
            $this->params['profile_id'] = $profileId;
        }

        return $this;
    }

    /**
     * Set the username for authenticating with Google Analytics to retrieve a
     * auth token. This is actually your email address you login to Google
     * Analytics with.
     *
     * @access  public
     * @param   string  $username
     * @return  $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set the password for authenticating with Google Analytics to retrieve a
     * auth token.
     *
     * @access  public
     * @param   string  $username
     * @return  $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Handle setting the report type.
     *
     * @access  public
     * @param   string  $type
     * @return  $this
     */
    public function setReportType($type)
    {
        $this->params['report_type'] = $type;

        return $this;
    }

    /**
     * Handle setting a start date.
     *
     * @access  public
     * @param   string  $start
     * @return  $this
     */
    public function setStartDate($start)
    {
        $this->params['start_date'] = $start;

        return $this;
    }

    /**
     * Handle setting a start date.
     *
     * @access  public
     * @param   string  $start
     * @return  $this
     */
    public function setEndDate($end)
    {
        $this->params['end_date'] = $end;

        return $this;
    }

    /**
     * Handle setting a start date.
     *
     * @access  public
     * @param   string  $groupby
     * @return  $this
     */
    public function setGroupBy($groupby = 'daily')
    {
        // sanitize group by as we set it
        $this->params['group_by'] = $this->groupBy($groupby);

        return $this;
    }

    /**
     * Handle setting a sort order. Must be called after we've issued
     * setGroupBy since it's a pre-req.
     *
     * @access  public
     * @param   string  $sortby
     * @return  $this
     */
    public function setSortBy($sortby = 'asc')
    {
        $this->params['sort_by'] = $this->sortBy($sortby);

        return $this;
    }

    /**
     * Handle generating an auth token. Note we change the timezone to UTC
     * to match up with Google.
     *
     * @access  public
     * @return  string
     */
    public function generateAuthToken()
    {
        // generate auth token
    	$tz = @date_default_timezone_get();
    	date_default_timezone_set('UTC');

        // return/set the auth token
        $token = $this->auth($this->getUsername(), $this->getPassword());
        if (!empty($token)) {
            $this->setAuthToken($token);
        } else {
            throw new Exception('An error occurred attempting to generate an auth token.');
        }

        // reset timezone back to default
        date_default_timezone_set($tz);

        return $this;
    }

    /**
     * Get the currently set username.
     *
     * @access  public
     * @return  string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the currently set password.
     *
     * @access  public
     * @return  string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Some general statistics on the audience.
     *
     * @access  public
     * @return  mixed
     */
    public function getAudienceStatistics()
    {
        $this->setReportType('audience');

        return $this->report('ga:visitors,ga:newVisits,ga:percentNewVisits,ga:visits,ga:bounces,ga:pageviews,ga:visitBounceRate,ga:timeOnSite,ga:avgTimeOnSite');
	}

    /**
     * Get traffic sources.
     *
     * https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
     *
     * @access  public
     * @return  mixed
     */
    public function getTrafficSources()
    {
        $this->setReportType('traffic-sources');

        return $this->report('ga:visits', $this->params['group_by'] . ',ga:medium');
    }

    /**
     * Get site referrers.
     *
     * https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
     *
     * @access  public
     * @return  mixed
     */
    public function getReferrers()
    {
        $this->setReportType('referrers');

        return $this->report(
            'ga:visits',
            $this->params['group_by'] . ',ga:source',
            $this->params['order_by'] . ',-ga:visits'
        );
    }

    /**
     * Get inbound keyword traffic results.
     *
     * https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
     *
     * @access  public
     * @return  mixed
     */
    public function getKeywords()
    {
        $this->setReportType('keywords');

        return $this->report(
            'ga:visits',
            $this->params['group_by'] . ',ga:keyword',
            $this->params['order_by'] . ',-ga:visits'
        );
	}

    /**
     * Get the page speed.
     *
     * @access  public
     * @return  mixed
     */
    public function getPageSpeed()
    {
        $this->setReportType('pagespeed');

        return $this->report(
            'ga:avgPageLoadTime,ga:avgDomainLookupTime,ga:avgPageDownloadTime,ga:avgServerConnectionTime,ga:avgServerResponseTime,ga:avgDomInteractiveTime,ga:avgDomContentLoadedTime'
        );
    }

    /**
     * Get some adword performance metrics by month and year.
     *
     * http://www.crescentinteractive.com/custom-adwords-apps-scripts/
     *
     * @access  public
     * @return  mixed
     */
    public function getAdwords()
    {
        $this->setReportType('adwords');

        return $this->report('ga:adclicks,ga:impressions,ga:cpc,ga:adCost');
    }

    /**
     * Visits by country.
     *
     * @access  public
     * @return  mixed
     */
	public function getVisitsByCountries()
    {
        $this->setReportType('visits-by-country');

        return $this->report(
            'ga:visits',
            $this->params['group_by'] . ',ga:country',
            $this->params['order_by'] . ',-ga:visits'
        );
	}

    /**
     * Visits by city.
     *
     * @access  public
     * @return  mixed
     */
	public function getVisitsByCities()
    {
        $this->setReportType('visits-by-city');

        return $this->report(
            'ga:visits',
            $this->params['group_by'] . ',ga:city',
            $this->params['order_by'] . ',-ga:visits'
        );
	}

    /**
     * Visits by language.
     *
     * @access  public
     * @return  mixed
     */
	public function getVisitsByLanguages()
    {
        $this->setReportType('visits-by-language');

        return $this->report(
            'ga:visits',
            $this->params['group_by'] . ',ga:language',
            $this->params['order_by'] . ',-ga:visits'
        );
	}

    /**
     * Visits by browser.
     *
     * @access  public
     * @return  mixed
     */
	public function getVisitsBySystemBrowsers()
    {
        $this->setReportType('visits-by-browser');

        return $this->report(
            'ga:visits',
            $this->params['group_by'] . ',ga:browser',
            $this->params['order_by'] . ',-ga:visits'
        );
	}

    /**
     * Visits by OS.
     *
     * @access  public
     * @return  mixed
     */
	public function getVisitsBySystemOs()
    {
        $this->setReportType('visits-by-os');

        return $this->report(
            'ga:visits',
            $this->params['group_by'] . ',ga:operatingSystem',
            $this->params['order_by'] . ',-ga:visits'
        );
	}

    /**
     * Visits by resolution.
     *
     * @access  public
     * @return  mixed
     */
	public function getVisitsBySystemResolutions()
    {
        $this->setReportType('visits-by-resolution');

        return $this->report(
            'ga:visits',
            $this->params['group_by'] . ',ga:screenResolution',
            $this->params['order_by'] . ',-ga:visits',
            'gaid::-11'
        );
	}

    /**
     * Visits by mobile OS.
     *
     * @access  public
     * @return  mixed
     */
	public function getVisitsByMobileOs()
    {
        $this->setReportType('visits-by-mobile');

        return $this->report(
            'ga:visits',
            $this->params['group_by'] . ',ga:operatingSystem',
            $this->params['order_by'] . ',-ga:visits',
            'gaid::-11'
        );
	}

    /**
     * Visits by mobile resolution.
     */
	public function getVisitsByMobileResolutions()
    {
        $this->setReportType('visits-by-mobile-resolution');

        return $this->report(
            'ga:visits',
            $this->params['group_by'] . ',ga:screenResolution',
            $this->params['order_by'] . ',-ga:visits',
            'gaid::-11'
        );
	}

    /**
     ***************************************************************************
     ***************************************************************************
     * System level functions below here that just pertain to auth and request(s).
     * No need to go below this line.
     ***************************************************************************
     ***************************************************************************
     */

    /**
     * Given a user friendly group by string, convert into a Google Analytics
     * appropriate group by string.
     *
     * @access  public
     * @param   string  $groupBy
     * @return  string
     */
    public function groupBy($groupBy)
    {
        if ($groupBy == 'hourly') {
            return 'ga:dateHour';
        } else if ($groupBy == 'daily') {
            return 'ga:date';
        } else if ($groupBy == 'weekly') {
            return 'ga:yearWeek';
        } else if ($groupBy == 'monthly') {
            return 'ga:yearMonth';
        } else if ($groupBy == 'yearly') {
            return 'ga:year';
        } else if ($groupBy == 'dayofweek') {
            return 'ga:dayOfWeek';
        }

        return 'ga:date';
    }

    /**
     * Given a user friendly group by string and a sort order, convert into a
     * Google Analytics appropriate sort by string.
     *
     * @access  public
     * @param   string  $orderBy
     * @return  string
     */
    public function sortBy($orderBy = 'asc')
    {
        $prefix = strtolower($orderBy) == 'asc' ? '' : '-';

        if (empty($this->params['group_by'])) {
            return $prefix . 'ga:date';
        }

        return $prefix . $this->params['group_by'];
    }

    /**
     * Handles basic authentication with Google Analytics that requires an
     * email address and a password. Not ideal as we don't want to be storing
     * this data for a user. To be deprecated in favor of OAUTH2 tokens.
     *
     * @access  public
     * @param   string  $email
     * @param   string  $password
     * @return  string  An auth token
     */
	public function auth($email, $password)
	{
		$response = $this->post(
			$this->loginUrl,
			array(
				'accountType'   => 'GOOGLE',
				'Email'         => $email,
				'Passwd'        => $password,
				'source'        => $this->interfaceName,
				'service'       => 'analytics'
			)
		);

		$str = str_replace(array("\n","\r\n"), '&', $response);
		parse_str($str, $auth);

		return $auth['Auth'];
	}

    /**
     * Run the actual report and return data.
     *
     * @access  public
     * @return  mixed
     */
	public function report($metrics, $dimensions = NULL, $sort = NULL, $segment = NULL)
	{
		$params = array();
		$params['ids']          = 'ga:' . $this->params['profile_id'];
		$params['start-date']   = $this->params['start_date'];
		$params['end-date']     = $this->params['end_date'];
		$params['metrics']      = $metrics;

        // check for dimensions (group by) override
		if (!empty($dimensions)) {
			$params['dimensions'] = $dimensions;
		} else {
            $params['dimensions'] = $this->params['group_by'];
        }

        // check for sort order override
        if (!empty($sort)) {
            $params['sort'] = $sort;
        } else {
            $params['sort'] = $this->params['sort_by'];
        }

        // check for segment(s)
        if (!empty($segment)) {
            $params['segment'] = $segment;
        }

        // max out our max results
        $params['max-results'] = 10000;

        // generate the request url
		$url = $this->reportUrl . '?' . http_build_query($params);

        // issue an API post request
		$response = $this->post($url, null);

        // return the API response XML as an array
		return xmlstr_to_array($response);
	}

    /**
     * Handle a simple POST request to the Google Analytics API.
     *
     * @access  public
     * @param   string  $url
     * @param   mixed   $vars
     * @return  mixed
     */
	public function post($url, $vars = null)
	{
        // generate auth token
    	$tz = @date_default_timezone_get();
    	date_default_timezone_set('UTC');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if (is_array($vars)) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		}

		if (!empty($this->params['auth_token'])) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: GoogleLogin auth=' . $this->params['auth_token']));
		}

        $response = curl_exec($ch);

        // reset timezone back to default
        date_default_timezone_set($tz);

        // return response
        return $response;
	}

}
