<?php
namespace MediaCore\Auth\OAuth;

use MediaCore\Auth\OAuth\SignatureMethod\HMAC_SHA1;

/**
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected $request;
    protected $consumer;
    protected $signatureMethod;
    protected $url;
    protected $params;

    /**
     */
    protected function setUp()
    {
        $key = 'myKey';
        $secret = 'mySecret';
        $this->consumer = new Consumer($key, $secret);
        $this->signatureMethod = new HMAC_SHA1();

        $this->url = 'https://example.com';
        $this->oauthParams = array(
            'oauth_version' => '1.0',
            'oauth_nonce' => 'd41d8cd98f00b204e9800998ecf8427e',
            'oauth_timestamp' => '1405011060',
        );
    }

    /**
     */
    protected function tearDown()
    {
        $this->consumer = null;
        $this->signatureMethod = null;
        $this->url = null;
        $this->oauthParams = null;
    }

    /**
     * TODO
     */
    public function testInvalidUrl()
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
