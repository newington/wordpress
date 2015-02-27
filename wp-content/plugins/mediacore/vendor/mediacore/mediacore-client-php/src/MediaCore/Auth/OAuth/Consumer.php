<?php
namespace MediaCore\Auth\OAuth;


/**
 * A basic OAuth consumer
 *
 * @category    MediaCore
 * @package     MediaCore\Auth\OAuth\Consumer
 * @copyright   Copyright (c) 2014 MediaCore Technologies Inc.
 *              (http://www.mediacore.com)
 * @license
 * @version     Release:
 * @link        https://github.com/mediacore/mediacore-client-php
 */
class Consumer
{
    /**
     * The consumer key
     *
     * @type string
     */
    private $_key = null;

    /**
     * The consumer secret
     *
     * @type string
     */
    private $_secret = null;

    /**
     * Constructor
     *
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->_key = $key;
        $this->_secret = $secret;
    }

    /**
     * Get the consumer key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Get the consumer secret
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }
}
