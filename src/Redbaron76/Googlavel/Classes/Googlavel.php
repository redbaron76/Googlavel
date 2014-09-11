<?php namespace Redbaron76\Googlavel\Classes;

use Illuminate\Session\Store;
use Illuminate\Config\Repository;
use Illuminate\Routing\Redirector;

use Google_Client;

class Googlavel {

    /**
     * The Google Client object
     * @var object
     */
    protected $client;

    /**
     * @var object
     */
    protected $config;

    /**
     * @var object
     */
    protected $session;

    /**
     * @var object
     */
    protected $redirect;

    /**
     * Services array
     * @var array
     */
    protected $service = [];

    /**
     * Initialize Googlavel class
     * @param Store      $session - the Laravel session object
     * @param Repository $config - the Laravel config object
     * @param Redirectory $redirect - the Laravel redirect object
     */
    public function __construct(Store $session, Repository $config, Redirector $redirect)
    {
        $this->config = $config;
        $this->session = $session;
        $this->redirect = $redirect;

        // Set the client as singleton
        if ( ! $this->client )
        {
            $this->client = $this->setupGoogleClient();
        }
    }

    /**
     * Authenticate with Google and get access token
     * @param  string $code - The OAuth2 callback code
     * @return bool
     */
    public function authenticate($code)
    {
        if ( $code )
        {
            $this->client->authenticate($code);

            $access_token = $this->client->getAccessToken();

            $this->session->put('googleapi_token', $access_token);

            return true;
        }

        return false;
    }

    /**
     * Logout, revoke access token and redirect to location
     * @return void or bool
     */
    public function logout($redirect = '/', $token = null)
    {
        $current_token = $token ?: $this->parseToken();

        if ( $redirect and $current_token )
        {
            $this->session->forget('googleapi_token');

            $this->client->revokeToken($current_token);

            return $this->redirect->to($redirect);
        }

        return false;
    }

    /**
     * Create a Auth url
     * @return string - The auth url
     */
    public function authUrl()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Get the client
     * @return object
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get config value
     * @param  string $key
     * @return string
     */
    public function getConfig($key)
    {
        return $this->config->get('googlavel::' . $key);
    }

    /**
     * Set config value 
     * @param string $key
     * @param string $value
     */
    public function setConfig($key, $value)
    {
        return $this->config->set('googlavel::' . $key, $value);
    }

    /**
     * Returns Google Service
     * @return object
     */
    public function getService($service = null)
    {
        if ( $service and ! array_key_exists($service, $this->service) )
        {
            $this->setService($service);
        }

        $this->setToken();

        return $this->service[$service];
    }

    /**
     * Set Google Service
     * @param void
     */
    public function setService($service)
    {
        $prefix = $this->getConfig('service_class_prefix');

        $this->service[$service] = $this->createInstance($service, $prefix, [$this->client]);
    }

    /**
     * Get the access token
     * @return string token
     */
    public function getToken()
    {
        return $this->session->get('googleapi_token');
    }

    /**
     * Get parsed token element
     * @param  $key - The key to parse
     * @return string - mixed key value
     */
    public function parseToken($key = 'access_token')
    {
        if ( $token = $this->getToken() )
        {
            $token_array = json_decode($token, true);

            return $token_array[$key];
        }
    }

    /**
     * Set an access token
     * @return  bool
     */
    public function setToken($token = null)
    {
        if ( ! $token and $this->session->has('googleapi_token') )
        {
            $token = $this->session->get('googleapi_token');
        }

        return $this->client->setAccessToken($token);
    }

    /**
     * Setup a new Google Client
     * @return object the Google_Client instance
     */
    private function setupGoogleClient()
    {
        $client = new Google_Client();

        $client->setClientId($this->getConfig('oauth2_client_id'));
        $client->setClientSecret($this->getConfig('oauth2_client_secret'));
        $client->setRedirectUri($this->getConfig('oauth2_redirect_uri'));
        $client->setScopes($this->getConfig('services'));
        $client->setAccessType($this->getConfig('access_type'));
        $client->setApprovalPrompt($this->getConfig('approval_prompt'));

        return $client;
    }

    /**
     * Instantiate a class with dependency classes 
     * @param  class $class
     * @param  array $params
     * @return object
     */
    private function createInstance($class, $class_prefix = '', $params = [])
    {
        $reflection_class = new \ReflectionClass($class_prefix . $class);

        return $reflection_class->newInstanceArgs($params);
    }

}