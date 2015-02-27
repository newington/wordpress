<?php
namespace MediaCore;

/**
 */
class UriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Uri
     */
    protected $uri;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers MediaCore\Uri::appendParam
     */
    public function testAppendParam()
    {
        $url = 'http://example.com/path/to/directory';
        $uri = new Uri($url);
        $uri->appendParam('foo', 'bar');
        $expectedValue = array('foo'=>'bar');
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());
    }

    /**
     * @covers MediaCore\Uri::appendParams
     */
    public function testAppendParams()
    {
        $url = 'http://example.com/path/to/directory';
        $uri = new Uri($url);
        $uri->appendParams(array('foo'=>'bar'));
        $expectedValue = array('foo'=>'bar');
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());
    }

    /**
     * @covers MediaCore\Uri::appendPath
     */
    public function testAppendPath()
    {
        $url = 'http://example.com/path/to/directory';
        $uri = new Uri($url);
        $uri->appendPath('/subdirectory');
        $expectedValue = '/path/to/directory/subdirectory';
        $this->assertEquals($expectedValue, $uri->getPath());
    }

    /**
     * @covers MediaCore\Uri::setPath
     */
    public function testSetPath()
    {
        $url = 'http://example.com/path/to/directory';
        $uri = new Uri($url);
        $uri->setPath('/subdirectory');
        $expectedValue = '/subdirectory';
        $this->assertEquals($expectedValue, $uri->getPath());
    }

    /**
     * @covers MediaCore\Uri::setParam
     */
    public function testSetParam()
    {
        $url = 'http://example.com/path/to/directory';
        $uri = new Uri($url);
        $uri->setParam('foo', 'bar');
        $expectedValue = array('foo'=>'bar');
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());

        $url = 'http://example.com/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $uri->setParam('foo', 'bar');
        $expectedValue = array('foo'=>'bar');
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());

        $url = 'http://example.com/path/to/directory?foo=bar&foo=baz';
        $uri = new Uri($url);
        $uri->setParam('foo', 'bar');
        $expectedValue = array('foo'=>array('bar', 'bar'));
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());
    }

    /**
     * @covers MediaCore\Uri::setQuery
     */
    public function testSetQuery()
    {
        $url = 'http://example.com/path/to/directory';
        $query = 'foo=bar&baz=qux';
        $uri = new Uri($url);
        $uri->setQuery($query);
        $this->assertEquals($query, $uri->getQuery());

        $url = 'http://example.com/path/to/directory?foo=bar&baz=qux';
        $query = 'foo=bar&baz=qux&foo=bar&baz=qux';
        $uri = new Uri($url);
        $uri->setQuery($query);
        $this->assertEquals($query, $uri->getQuery());
    }

    /**
     * @covers MediaCore\Uri::removeParam
     */
    public function testRemoveParam()
    {
        $url = 'http://example.com/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $uri->removeParam('foo');
        $expectedValue = array();
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());

        $url = 'http://example.com/path/to/directory?foo=bar&foo=baz';
        $uri = new Uri($url);
        $uri->removeParam('foo');
        $expectedValue = array();
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());
    }

    /**
     * @covers MediaCore\Uri::getScheme
     */
    public function testGetScheme()
    {
        $url = 'http://example.com/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $this->assertEquals('http', $uri->getScheme());
    }

    /**
     * @covers MediaCore\Uri::getHost
     */
    public function testGetHost()
    {
        $url = 'http://example.com/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $this->assertEquals('example.com', $uri->getHost());

        $url = 'http://example.com:8080/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $this->assertEquals('example.com', $uri->getHost());
    }

    /**
     * @covers MediaCore\Uri::getPort
     */
    public function testGetPort()
    {
        $url = 'http://example.com:8080/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $this->assertEquals('8080', $uri->getPort());

        $url = 'http://example.com/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $this->assertNull($uri->getPort());
    }

    /**
     * @covers MediaCore\Uri::getPath
     */
    public function testGetPath()
    {
        $url = 'http://example.com:8080/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $this->assertEquals('/path/to/directory', $uri->getPath());
    }

    /**
     * @covers MediaCore\Uri::getFragment
     */
    public function testGetFragment()
    {
        $url = 'http://www.example.org/foo.html#bar';
        $uri = new Uri($url);
        $this->assertEquals('bar', $uri->getFragment());
    }

    /**
     * @covers MediaCore\Uri::getQuery
     */
    public function testGetQuery()
    {
        $url = 'http://example.com/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $expectedValue = 'foo=bar';
        $this->assertEquals($expectedValue, $uri->getQuery());

        $url = 'http://example.com/path/to/directory?foo=bar&foo=baz';
        $uri = new Uri($url);
        $expectedValue = 'foo=bar&foo=baz';
        $this->assertEquals($expectedValue, $uri->getQuery());
    }

    /**
     * @covers MediaCore\Uri::getQueryAsArray
     */
    public function testGetQueryAsArray()
    {
        //simple query string
        $url = 'http://example.com/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $expectedValue = array('foo'=>'bar');
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());

        //multiple query params
        $url = 'http://example.com/path/to/directory?foo=bar&foo=baz';
        $uri = new Uri($url);
        $expectedValue = array('foo'=>array('bar','baz'));
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());

        //foo=bar=baz
        $url = 'http://example.com/path/to/directory?foo=bar%3Dbaz';
        $uri = new Uri($url);
        $expectedValue = array('foo'=>'bar=baz');
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());

        //empty query param value
        $url = 'http://example.com/path/to/directory?foo=bar&baz=';
        $uri = new Uri($url);
        $expectedValue = array('foo'=>'bar','baz'=>'');
        $this->assertEquals($expectedValue, $uri->getQueryAsArray());
    }

    /**
     * @covers MediaCore\Uri::getParamValue
     */
    public function testGetParamValue()
    {
        $url = 'http://example.com/path/to/directory?foo=bar';
        $uri = new Uri($url);
        $expectedValue = 'bar';
        $this->assertEquals($expectedValue, $uri->getParamValue('foo'));

        $url = 'http://example.com/path/to/directory?foo=bar&foo=baz';
        $uri = new Uri($url);
        $expectedValue = array('bar','baz');
        $this->assertEquals($expectedValue, $uri->getParamValue('foo'));
    }

    /**
     * @covers MediaCore\Uri::buildQuery
     */
    public function testBuildQuery()
    {
        //test nested arrays
        $params = array(
            'foo'=>'bar',
            'foo'=>array('bar','baz'),
        );
        $encodedQuery = Uri::buildQuery($params);
        $expectedValue = 'foo=bar&foo=baz';
        $this->assertEquals($expectedValue, $encodedQuery);

        //test query encoding
        $params = array(
            'foo with spaces'=>'bar',
            'bar'=>array('baz with spaces'),
        );
        $encodedQuery = Uri::buildQuery($params);
        $expectedValue = 'foo%20with%20spaces=bar&bar=baz%20with%20spaces';
        $this->assertEquals($expectedValue, $encodedQuery);

        //test multiple args
        $params1 = array(
            'foo'=>'bar',
            'foo'=>array('bar','baz'),
        );
        $params2 = array(
            'foo2'=>'bar2',
            'foo2'=>array('bar2','baz2'),
        );
        $encodedQuery = Uri::buildQuery($params1, $params2);
        $expectedValue = 'foo=bar&foo=baz&foo2=bar2&foo2=baz2';
        $this->assertEquals($expectedValue, $encodedQuery);
    }

    /**
     * @covers MediaCore\Uri::hasParam
     */
    public function testHasParam()
    {
        $url = 'http://example.com/path/to/directory?foo=bar&foo=baz';
        $uri = new Uri($url);
        $this->assertTrue($uri->hasParam('foo'));
    }

    /**
     * @covers MediaCore\Uri::toString
     */
    public function testToString()
    {
        $url = 'http://example.com/path/to/directory?foo=bar&foo=baz';
        $uri = new Uri($url);
        $this->assertEquals($url, $uri->toString());
    }
}
