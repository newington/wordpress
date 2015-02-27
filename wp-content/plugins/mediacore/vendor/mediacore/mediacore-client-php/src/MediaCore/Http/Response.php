<?php
namespace MediaCore\Http;


/**
 * A basic HTTP Response based on Requests_Response
 *
 * @category    MediaCore
 * @package     MediaCore\Http\Response
 * @subpackage
 * @copyright   Copyright (c) 2014 MediaCore Technologies Inc.
 *              (http://www.mediacore.com)
 * @license
 * @version     Release:
 * @link        https://github.com/mediacore/mediacore-client-php
 */
class Response
{
    /**
     * The response body
     *
     * @type string
     */
    public $body;

    /**
     * The response status code
     *
     * @type number
     */
    public $statusCode;

    /**
     * Whether the response was a 201
     *
     * @type bookean
     */
    public $success;

    /**
     * The response url
     *
     * @type string
     */
    public $url;

    /**
     * The response json
     *
     * @type object
     */
    public $json;

    /**
     * The response headers
     *
     * @type \Request_Response_Headers
     */
    private $_headers = null;

    /**
     * The response cookies
     *
     * @type \Requests_Cookie_Jar
     */
    private $_cookies = null;

    /**
     * Constructor
     *
     * @param Requests_Response $response
     */
    public function __construct(\Requests_Response $response)
    {
        $this->_headers = $response->headers;
        $this->_cookies = $response->cookies;
        $this->body = $response->body;
        $this->statusCode = $response->status_code;
        $this->success = $response->success;
        $this->url = $response->url;
        $this->json = $this->parseJson();
    }

    /**
     * Get a header by its key
     *
     * @param string $key
     * @return string
     */
    public function getHeader($key)
    {
        return $this->_headers->offsetGet($key);
    }

    /**
     * Get all headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return iterator_to_array($this->_headers);
    }

    /**
     * Get a cookie value by key
     *
     * @param string $key
     * @return string
     */
    public function getCookie($key)
    {
        return $this->_cookies->offsetGet($key);
    }

    /**
     * Get all cookies
     *
     * @return array
     */
    public function getCookies()
    {
        return iterator_to_array($this->_cookies);
    }

    /**
     *
     * @return object|null
     */
    private function parseJson()
    {
        if (!isset($this->body)) {
            return null;
        }
        $contentType = $this->getHeader('content-type');
        if ($contentType !== 'application/json; charset=utf-8') {
            return null;
        }
        return json_decode($this->body);
    }
}
