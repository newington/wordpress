<?php
namespace MediaCore\Auth;

/**
 * A basic LTI request builder
 *
 * @category    MediaCore
 * @package     MediaCore\Auth\Lti
 * @subpackage
 * @copyright   Copyright (c) 2014 MediaCore Technologies Inc.
 *              (http://www.mediacore.com)
 * @license
 * @version     Release:
 * @link        https://github.com/mediacore/mediacore-client-php
 */
class Lti implements \Requests_Auth
{
    /**
     * The LTI version
     *
     * @type null|string
     */
    const VERSION = 'LTI-1p0';

    /**
     * The LTI consumer key
     *
     * @type string
     */
    private $key;

    /**
     * The LTI consumer secret
     *
     * @type string
     */
    private $secret;

    /**
     * Lti signature method
     *
     * @type null|HMAC_SHA1
     */
    private $signatureMethod;

    /**
     * The consumer
     *
     * @type null|OAuthConsumer
     */
    private $consumer;

    /**
     * Constructor
     *
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->consumer = new OAuth\Consumer($this->key, $this->secret);
        $this->signatureMethod = new OAuth\SignatureMethod\HMAC_SHA1();
    }

    /**
     */
    public function register(\Requests_Hooks &$hooks)
    {
        $hooks->register('requests.before_request',
            array(&$this, 'beforeRequest'));
    }

    /**
     */
    public function beforeRequest(&$url, &$headers, &$data, &$type, &$options)
    {
        $url = $this->buildRequestUrl($url, $type, $data);
        if ($type != 'GET') {
            $uri = new \MediaCore\Uri($url);
            $data = $uri->getQueryAsArray();
            $uri->setQuery('');
            $url = $uri->toString();
        }
    }

    /**
     * Build the LTI request using LTI params passed in as arguments
     *
     * @param string $url
     * @param string $method
     * @param array $params
     *
     * @return string
     */
    public function buildRequestUrl($url, $method, $params)
    {
        $params['lti_version'] = $this->getVersion();
        $request = new OAuth\Request($this->consumer, $url, $method, $params);
        return $request->signRequest($this->signatureMethod);
    }

    /**
     * Get the LTI version
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }
}
