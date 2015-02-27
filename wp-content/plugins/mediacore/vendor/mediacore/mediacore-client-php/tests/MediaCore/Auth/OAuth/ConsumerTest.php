<?php
namespace MediaCore\Auth\Oauth;

/**
 */
class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     */
    protected function setUp()
    {
        $this->key = 'myKey';
        $this->secret = 'mySecret';
        $this->consumer = new Consumer($this->key, $this->secret);
    }

    /**
     */
    protected function tearDown()
    {
        $this->key = null;
        $this->secret = null;
        $this->consumer = null;
    }

    /**
     * @covers MediaCore\OAuth\Request::getKey
     */
    public function testKey()
    {
        $this->assertEquals('myKey', $this->consumer->getKey());
    }

    /**
     * @covers MediaCore\OAuth\Consumer::getSecret
     */
    public function testSecret()
    {
        $this->assertEquals('mySecret', $this->consumer->getSecret());
    }
}
