<?php
namespace MediaCore\Auth\OAuth\SignatureMethod;

use MediaCore\Auth\OAuth\Consumer;

/**
 */
class HMAC_SHA1Test extends \PHPUnit_Framework_TestCase
{
    protected $service;
    protected $name;
    protected $consumer;

    /**
     */
    protected function setUp()
    {
        $this->name = 'HMAC-SHA1';
        $this->signatureMethod = new HMAC_SHA1();
        $key = 'myKey';
        $secret = 'mySecret';
        $this->consumer = new Consumer($key, $secret);
    }

    /**
     */
    protected function tearDown()
    {
        $this->name = null;
        $this->signatureMethod = null;
        $this->consumer = null;
    }

    /**
     * @covers MediaCore\OAuth\SignatureMethod\HMAC_SHA1::getName
     */
    public function testGetName()
    {
        $expectedValue = $this->name;
        $this->assertEquals($expectedValue,
            $this->signatureMethod->getName());
    }

    /**
     * @covers MediaCore\OAuth\SignatureMethod\HMAC_SHA1::buildSignature
     */
    public function testBuildSignature()
    {
        $baseString = 'GET&http%3A%2F%2Ffakeurl.com&key%3Dvalue';
        $signature = $this->signatureMethod->buildSignature($this->consumer, $baseString);
        $expectedValue = 'QgqnPt+Nj+TPBSokBdVBeifpyYM=';
        $this->assertEquals($expectedValue, $signature);
    }
}
