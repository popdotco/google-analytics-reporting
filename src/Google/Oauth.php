<?php
namespace Google;

/**
 * A simple OAUTH2 library for authenticating users with the Google Analytics API.
 * 
 * Based off of the hard work of:
 * https://github.com/wanze/Google-Analytics-API-PHP
 */
class Oauth {

    /**
     * The url to generate tokens.
     * @var string
     */
    const TOKEN_URL = 'https://accounts.google.com/o/oauth2/token';

    /**
     * The necessary scope we need access to for reading Google Anayltics data.
     * @var string
     */
    const SCOPE_URL = 'https://www.googleapis.com/auth/analytics.readonly';

    /**
     * The url to authenticate via OAUTH2.
     * @var string
     */
    const AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';

    /**
     * The OAUTH2 revokation url.
     * @var string
     */
    const REVOKE_URL = 'https://accounts.google.com/o/oauth2/revoke';

    /**
     * The client id.
     * @var string
     */
    protected $clientId = '';

    /**
     * The client secret.
     * @var string
     */
    protected $clientSecret = '';

    /**
     * The redirect url when a user is authenticating.
     * @var string
     */
    protected $redirectUri = '';

    /**
     * The default constructor for setting up the necessary params to enable
     * authentication for users.
     *
     * @access  public
     * @param   string  $clientId       Client-ID of your web application from the Google APIs console
     * @param   string  $clientSecret   Client-Secret of your web application from the Google APIs console
     * @param   string  $redirectUri    Redirect URI to your app - must match with an URL provided in the Google APIs console
     */
    public function __construct($clientId = '', $clientSecret = '', $redirectUri = '')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    /**
     * Magic setter.
     */
    public function __set($key, $value)
    {
        $this->{$key} = $value;
    }

    /**
     * Set the client id.
     *
     * @access  public
     * @param   string  $id
     * @return  void
     */
    public function setClientId($id)
    {
        $this->clientId = $id;
    }

    /**
     * Set the client secret.
     *
     * @access  public
     * @param   string  $secret
     * @return  void
     */
    public function setClientSecret($secret)
    {
        $this->clientSecret = $secret;
    }

    /**
     * Set the client redirect uri.
     *
     * @access  public
     * @param   string  $uri
     * @return  void
     */
    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;
    }

    /**
     * Build the authentication url with the redirect uri. The user will be
     * redirected to Google to login and then redirected back to the redirect uri
     * upon successful login. At this point, a parameter will contain the final
     * auth token to store locally.
     *
     * @access  public
     * @param   array   $params Any param overrides we may want
     * @return  string  The auth login-url
     */
    public function buildAuthUrl($params = array())
    {
        // some simple validation
        if (empty($this->clientId) || empty($this->redirectUri)) {
            throw new Exception('You must provide a clientId and redirectUri.');
        }

        $defaults = array(
            'response_type'     => 'code',
            'client_id'         => $this->clientId,
            'redirect_uri'      => $this->redirectUri,
            'scope'             => self::SCOPE_URL,
            'access_type'       => 'offline',
            'approval_prompt'   => 'force'
        );

        // allow for overrides when generating the auth url
        $params = array_merge($defaults, $params);

        // return the generated url
        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Get the AccessToken in exchange with the code from the auth along with a refreshToken
     *
     * @access  public
     * @param   mixed   $data   The code received with GET after auth
     * @return  array           Array with the following keys: access_token, refresh_token, expires_in
     */
    public function getAccessToken($data = null)
    {
        if (empty($this->clientId) || empty($this->clientSecret) || empty($this->redirectUri)) {
            throw new Exception('You must provide a clientId, clientSecret, and redirectUri.');
        }

        $params = array(
            'code'          => $data,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => 'authorization_code',
        );

        $auth = $this->post(GoogleOauth::TOKEN_URL, $params, true);

        // return associative array
        return json_decode($auth, true);
    }

    /**
     * Given an access token which also has it's created date stored, determine
     * if the token has expired or not so we can issue a refresh.
     *
     * @access  public
     * @param   array   $accessToken
     * @return  bool
     */
    public function isTokenExpired($accessToken)
    {
        if (time() >= $accessToken['expires_at']) {
            return true;
        }

        return false;
    }

    /**
     * Get a new accessToken with the refreshToken if the current token has
     * expired.
     *
     * @access  public
     * @param   mixed   $refreshToken   The refreshToken
     * @return  array                   Array with keys: access_token, expires_in
     */
    public function refreshAccessToken($refreshToken)
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception('You must provide a clientId and clientSecret.');
        }

        $params = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        );

        $auth = $this->request(GoogleOauth::TOKEN_URL, $params, true);

        // prepare the token
        $token = json_decode($auth, true);
        if (empty($token)) {
            return;
        }

        $time = time();
        $token['created_at'] = $time;
        $token['expires_at'] = $time + $token['expires_in'];

        return $token;
    }


    /**
     * Revoke access for a user.
     *
     * @access  public
     * @param   mixed   $token  Either an accessToken or refreshToken.
     * return   mixed
     */
    public function revokeAccess($token)
    {
        $params = array('token' => $token);
        $data = $this->request(self::REVOKE_URL, $params);
        return json_decode($data, true);
    }

    /**
     * Make a cURL request to Google OAUTH endpoint(s).
     *
     * @access  public
     * @param   string  $url
     * @param   array   $params
     * @param   bool    $post
     */
    public function request($url, $params = array(), $post = FALSE)
    {
        if (!$post && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $data = curl_exec($ch);

        // get the response code
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // close curl
        curl_close($curl);

        // add the response code to the json response
        if (!empty($data)) {
            return preg_replace('/^{/', '{"http_code":' . $http_code . ',', $data);
        }

        return false;
    }

}
