<?php
namespace MediaCore;

use \Zend\Uri\Uri as Zend_Uri;


/**
 * A Uri helper class that uses \Zemd\Uri\Uri
 * under the hood and adds some additional useful
 * url methods.
 *
 * NOTE: This class changes the way url query params
 * are interpreted and built. It accepts duplicate
 * query params and does not interpret params
 * that use square bracket notation. When calling
 * getQueryAsArray(), duplicate query params are
 * assembled in nested arrays keyed to the param key
 *
 * @category    MediaCore
 * @package     MediaCore\Uri
 * @subpackage
 * @copyright   Copyright (c) 2014 MediaCore Technologies Inc.
 *              (http://www.mediacore.com)
 * @license
 * @version     Release:
 * @link        https://github.com/mediacore/mediacore-client-php
 */
class Uri
{
    /**
     * The uri object
     *
     * @type null|Zend\Uri\Uri
     */
    private $_uri = null;

    /**
     * Constructor
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->_uri = new Zend_Uri(rtrim($url, '/'));
        // NOTE: Normalizing a URI includes removing any redundant
        // parent directory or current directory references from the path
        // (e.g. foo/bar/../baz becomes foo/baz), normalizing the scheme
        // case, decoding any over-encoded characters etc.
        $this->_uri->normalize();
    }

    /**
     * Encode and append a param to the url
     *
     * @param string $key
     * @param string $value
     * @return Uri
     */
    public function appendParam($key, $value)
    {
        return $this->appendParams(array($key=>$value));
    }

    /**
     * Encode and append a list of params to the url
     *
     * @param array $params Key/Value pairs
     * @return Uri
     */
    public function appendParams($params)
    {
        $queryStr = $this->_uri->getQuery();
        if (!empty($queryStr)) {
            $queryStr .= '&';
        }
        $queryStr .= self::buildQuery($params);
        $this->_uri->setQuery($queryStr);
        return $this;
    }

    /**
     * Append a path to the url
     *
     * @param string $path
     * @return Uri
     */
    public function appendPath($path)
    {
        $currPath = $this->_uri->getPath();
        $path = $currPath . '/' . $path;
        $path = $this->normalizePath($path);
        $this->_uri->setPath($path);
        return $this;
    }

    /**
     * Normalize the path string
     * NOTE: This will always omit the trailing
     * / (slash)
     *
     * @param string $path
     * @return string
     */
    public function normalizePath($path)
    {
        $path = '/' . rtrim($path, '/');
        // replace any 2 or more forward slashes
        // with a single forward slash
        return preg_replace('/[\/]{2,}/', '/', $path);
    }

    /**
     * Set the url path
     *
     * @param string $path
     * @return Uri
     */
    public function setPath($path)
    {
        $path = $this->normalizePath($path);
        $this->_uri->setPath($path);
        return $this;
    }

    /**
     * Replace all existing parameters with its value
     * encoded
     *
     * @param string $key
     * @param string $value
     * @return Uri
     */
    public function setParam($key, $value)
    {
        $params = $this->getQueryAsArray(/* encoded */ false);
        if (array_key_exists($key, $params)) {
            if (is_array($params[$key])) {
                //replace all occurences
                foreach ($params[$key] as &$val) {
                    $val = $value;
                }
            } else {
                $params[$key] = $value;
            }
        } else {
            $params[$key] = $value;
        }
        $queryStr = self::buildQuery($params);
        $this->_uri->setQuery($queryStr);
        return $this;
    }

    /**
     * Set the url query
     *
     * @param string|array $query
     */
    public function setQuery($query)
    {
        if (is_array($query)) {
            $queryStr = self::buildQuery($query);
            $this->_uri->setQuery($queryStr);
        } else {
            $this->_uri->setQuery($query);
        }
        return $this;
    }

    /**
     * Remove all occurences of a parameter
     *
     * @param string $key
     * @return Uri
     */
    public function removeParam($key)
    {
        $params = $this->getQueryAsArray(/* encoded */ false);
        if (array_key_exists($key, $params)) {
            unset($params[$key]);
        }
        $queryStr = self::buildQuery($params);
        $this->_uri->setQuery($queryStr);
        return $this;
    }

    /**
     * Get the url scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->_uri->getScheme();
    }

    /**
     * Get the url host (no port)
     *
     * @return string
     */
    public function getHost()
    {
        return $this->_uri->getHost();
    }

    /**
     * Get the url port
     *
     * @return null|string
     */
    public function getPort()
    {
        return $this->_uri->getPort();
    }

    /**
     * Get the url path
     *
     * @return null|string
     */
    public function getPath()
    {
        return $this->_uri->getPath();
    }

    /**
     * Get the url fragment
     *
     * @return null|string
     */
    public function getFragment()
    {
        return $this->_uri->getFragment();
    }

    /**
     * Get the url query
     *
     * @return null|string
     */
    public function getQuery()
    {
        return $this->_uri->getQuery();
    }

    /**
     * Replacement for parse_str so that it doesn't use square
     * bracket notation for duplicate query params
     *
     * @return array
     */
    public function getQueryAsArray($encoded=false)
    {
        $queryStr = $this->getQuery();
        if (empty($queryStr)) {
            return array();
        }
        $pairs = explode('&', $queryStr);
        $result = array();
        foreach ($pairs as $p) {
            $kv = explode('=', $p, 2);
            $key = $kv[0];
            $val = $kv[1];
            if (!$encoded) {
                $key = urldecode($key);
                $val = urldecode($val);
            }
            if (array_key_exists($key, $result)) {
                if (is_array($result[$key])) {
                    array_push($result[$key], $val);
                } else {
                    $currVal = $result[$key];
                    $result[$key] = array($currVal,$val);
                }
            } else {
                $result[$key] = $val;
            }
        }
        return $result;
    }

    /**
     * Get a query param's value(s)
     *
     * @param string $key
     * @return null|string|array
     */
    public function getParamValue($key)
    {
        $params = $this->getQueryAsArray(/* encoded */ false);
        if (array_key_exists($key, $params)) {
            return $params[$key];
        }
        return null;
    }

    /**
     * Replacement for http_build_query so that it reliably percent-encodes
     * params and doesn't use the square bracket notation for duplicates
     *
     * @param ... $params Any number of associative arrays of params
     * @return string
     */
    public static function buildQuery()
    {
        $args = func_get_args();
        if (empty($args)) {
            return '';
        }
        $queryStr = '';
        foreach ($args as $arg) {
            foreach ($arg as $key=>$value) {
                $key = urlencode($key);
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $val = urlencode($v);
                        $queryStr .= $key . '=' . $val . '&';
                    }
                } else {
                    $value = urlencode($value);
                    $queryStr .= $key . '=' . $value . '&';
                }
            }
        }
        $queryStr = str_replace('%7E', '~', $queryStr);
        $queryStr = str_replace('+', '%20', $queryStr);
        return rtrim($queryStr, '&');
    }


    /**
     * Check if a url query param existso
     *
     * @param string $key
     */
    public function hasParam($key)
    {
        $val = $this->getParamValue($key);
        return isset($val);
    }

    /**
     * Validate the URL
     *
     * @return boolean
     */
    public function isValid()
    {
        if (!$this->_uri->getScheme()) {
            return false;
        }
        if (!$this->_uri->getHost()) {
            return false;
        }
        return true;
    }

    /**
     * Compose the URI into a string
     *
     * @return string
     */
    public function toString()
    {
        return $this->_uri->toString();
    }

    /**
     * Magic method to convert the URI to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
