<?php
namespace MediaCore\Http;

use MediaCore\Uri;
use MediaCore\Exception;

/**
 * A basic HTTP client that uses Requests_Request
 *
 * @category    MediaCore
 * @package     MediaCore\Http\Client
 * @subpackage
 * @copyright   Copyright (c) 2014 MediaCore Technologies Inc.
 *              (http://www.mediacore.com)
 * @license
 * @version     Release:
 * @link        https://github.com/mediacore/mediacore-client-php
 */
class Client
{
    /**
     * GET method
     *
     * @var string
     */
    const GET = 'GET';

    /**
     * POST method
     *
     * @var string
     */
    const POST = 'POST';

    /**
     * PUT
     *
     * @var string
     */
    const PUT = 'PUT';

    /**
     * DELETE method
     *
     * @var string
     */
    const DELETE = 'DELETE';

    /**
     * The uri
     *
     * @var Uri
     */
    private $_uri = '';

    /**
     * The auth object
     *
     * @var null|\Requests_Auth
     */
    private $_auth = null;

    /**
     * Constructor
     *
     * @param string $url
     * @param Requests_Auth $auth
     */
    public function __construct($url, $auth=null)
    {
        $this->_uri = new Uri(rtrim($url, '/'));
        if (isset($auth) && $auth instanceof \Requests_Auth) {
            $this->_auth = $auth;
        }
    }

    /**
     * Set the auth used for requests
     *
     * @param \Requests_Auth $auth
     */
    public function setAuth($auth)
    {
        if (!($auth instanceof \Requests_Auth)) {
            throw new Exception(sprintf(
                'Expecting an instance of Requests_Auth, received "%s"',
                (is_object($auth) ? get_class($auth) : gettype($auth))
            ));
        }
        $this->_auth = $auth;
    }

    /**
     * Clear the auth used for these requests
     */
    public function clearAuth()
    {
        $this->_auth = null;
    }

    /**
     * Get the auth object
     *
     * @return \Requests_Auth|null
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * Get a url based on passed url segments
     *
     * @param ...
     */
    public function getUrl()
    {
        $path = '';
        $args = array_filter(func_get_args());
        if (is_array($args) && !empty($args)) {
            $path .= '/'. implode('/', $args);
        }
        $uri = clone $this->_uri;
        return $uri->setPath($path)->toString();
    }

    /**
     * Build a percent encoded query string
     * from an array of un-encoded params
     * (key/values) pairs
     *
     * @param array $params
     */
    public function getQuery($params)
    {
        return Uri::buildQuery($params);
    }

    /**
     * Send a GET request
     *
     * @param string $url
     * @param array $headers
     * @param array $options
     * @return \MediaCore\Response
     */
    public function get($url, $headers=array(), $options=array())
    {
        return $this->send($url, self::GET, /* data */ null, $headers, $options);
    }

    /**
     * Send a POST request
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return \MediaCore\Response
     */
    public function post($url, $data=array(), $headers=array(), $options=array())
    {
        return $this->send($url, self::POST, $data, $headers, $options);
    }

    /**
     * Send a PUT request
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return \MediaCore\Response
     */
    public function put($url, $data=array(), $headers=array(), $options=array())
    {
        return $this->send($url, self::PUT, $data, $headers, $options);
    }

    /**
     * Send a DELETE request
     *
     * @param string $url
     * @param array $headers
     * @param array $options
     * @return \MediaCore\Response
     */
    public function delete($url, $headers=array(), $options=array())
    {
        return $this->send($url, self::DELETE, null, $headers, $options);
    }

    /**
     * Send a request
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $headers
     * @param array $options
     */
    public function send($url, $method=self::GET, $data=array(),
        $headers=array(), $options=array())
    {
        if (isset($this->_auth)) {
            $options['auth'] = $this->_auth;
        }
        try {
            $response = \Requests::request($url, $headers, $data, $method, $options);
            return new Response($response);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
