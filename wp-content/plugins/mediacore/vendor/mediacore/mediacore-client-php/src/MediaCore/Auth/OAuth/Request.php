<?php
namespace MediaCore\Auth\OAuth;

/**
 * An Oauth Request
 *
 * @category    MediaCore
 * @package     MediaCore\Auth\OAuth\Request
 * @copyright   Copyright (c) 2014 MediaCore Technologies Inc.
 *              (http://www.mediacore.com)
 * @license
 * @version     Release:
 * @link        https://github.com/mediacore/mediacore-client-php
 */
class Request
{
    /**
     * The OAuth version
     *
     * @var string
     */
    const OAUTH_VERSION = '1.0';

    /**
     * The consumer
     *
     * @var null|OAuth\Consumer
     */
    private $_consumer = null;

    /**
     * The uri
     *
     * @var null|\Zend\Uri\Uri
     */
    private $_uri = null;

    /**
     * The request method
     *
     * @var null|string
     */
    private $_method = null;

    /**
     * Constructor
     *
     * @param OAuth\Consumer $consumer
     * @param string $url
     * @param string $method
     * @param array $params
     */
    public function __construct($consumer, $url, $method, $params=array())
    {
        $this->_consumer = $consumer;
        $this->_method = $method;

        //normalize the uri: remove the query params
        //and store them alonside the oauth and user params
        $this->_uri = new \MediaCore\Uri($url);
        $this->_queryParams = $this->_uri->getQueryAsArray(/* encoded */ false);
        $this->_uri->setQuery('');

        $this->_oAuthParams = $this->_getOAuthParams();
        $this->_params = $params;
    }

    /**
     * Create the oauth signature method and signature string
     * and return the signed request url
     *
     * @param MediaCore\OAuth\SignatureMethod\HMAC_SHA1 $signatureMethod
     * @return array
     */
    public function signRequest($signatureMethod)
    {
        $this->_oAuthParams['oauth_signature_method'] = $signatureMethod->getName();
        $signature = $signatureMethod->buildSignature(
            $this->_consumer, $this->_getBaseString());
        $uri = clone $this->_uri;
        $queryStr = $this->_concatQueryParams();
        $uri->setQuery($queryStr);
        return $uri->toString() . '&oauth_signature=' . $signature;
    }

    /**
     * Get the query string
     *
     * @return string
     */
    public function getQueryStr()
    {
        return $this->_concatQueryParams();
    }

    /**
     * Encode the HTTP method, base URL, and parameter string into a
     * single string
     *
     * @return string
     */
    private function _getBaseString() {
        $baseStrings = array();

        //method
        $baseStrings[] = strtoupper($this->_method);

        //base url (scheme://host:port/path)
        $uri = clone $this->_uri;
        $baseUrl = $uri->toString();
        $baseStrings[] = rawurlencode($baseUrl);

        //query str
        $queryStr = $this->_concatQueryParams();
        $uri->setQuery($queryStr);
        $orderedParamArray = $this->toByteOrderedValueQueryString(
            $uri->getQueryAsArray(/* encoded */ true)
        );
        $baseStrings[] = rawurlencode($orderedParamArray);

        return implode('&', $baseStrings);
    }

    /**
     * Append all params to the query str
     */
    private function _concatQueryParams() {
        return \MediaCore\Uri::buildQuery(
            $this->_queryParams,
            $this->_oAuthParams,
            $this->_params
        );
    }

    /**
     * Sort the encoded parameters by a "natural order"
     * algorithm (lexicographical byte value ordering).
     * http://oauth.net/core/1.0/ (Section 9.1.1)
     * Borrowed from ZF1.12:
     * @link Zend_OAuth_Signature_SignatureAbstract
     *
     * @param array $params
     * @return string
     */
    private function toByteOrderedValueQueryString($params)
    {
        uksort($params, 'strnatcmp');
        $pairs = array();
        foreach ($params as $key=>$value) {
            if (is_array($value)) {
                natsort($value);
                foreach ($value as $dup) {
                    $pairs[] = $key . '=' . $dup;
                }
            } else {
                $pairs[] = $key . '=' . $value;
            }
        }
        return implode('&', $pairs);
    }

    /**
     * Get the OAuth parameter defaults
     *
     * @return array
     */
    private function _getOAuthParams() {
        return array(
            'oauth_version' => self::OAUTH_VERSION,
            'oauth_nonce' => $this->_generateNonce(),
            'oauth_timestamp' => $this->_generateTimestmp(),
            'oauth_consumer_key' => $this->_consumer->getKey(),
        );
    }

    /**
     * Generate the oauth_nonce string
     *
     * @return string
     */
    private function _generateNonce()
    {
        $mtime = microtime();
        $rand = mt_rand();
        return md5($mtime.$rand);
    }

    /**
     * Generate the current timestamp
     *
     * @return string
     */
    private function _generateTimestmp()
    {
        return time();
    }

    /**
     * Get the OAuth version
     *
     * @return string
     */
    public function getOAuthVersion()
    {
        return self::OAUTH_VERSION;
    }
}
