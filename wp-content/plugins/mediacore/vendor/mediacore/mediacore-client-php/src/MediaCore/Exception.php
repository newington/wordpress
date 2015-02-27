<?php
namespace MediaCore;

/**
 * A basic Exception class
 * NOTE: Borrowed from ZendFramework (1.12) for
 * its elegant fallback support for PHP < 5.3.0
 *
 * @category    MediaCore
 * @package     Exception
 * @subpackage
 * @license
 * @version     Release:
 * @link        https://github.com/mediacore/mediacore-client-php
 */
class Exception extends \Exception
{
    /**
     * The previous exception
     *
     * @type null|\Exception
     */
    private $_previous = null;

    /**
     * Constructor
     *
     * @param string $message
     * @param int $code
     * @param Exception $previous
     * @return void
     */
    public function __construct($message, $code=0, Exception $previous=null)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            parent::__construct($message, (int)$code);
            $this->_previous = $previous;
        } else {
            parent::__construct($message, (int)$code, $previous);
        }
    }

    /**
     * Support for PHP < 5.3.0 getPrevious method
     * NOTE: only called when a method isn't found
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, array $args)
    {
        if (strtolower($method) == 'getprevious') {
            return $this->_getPrevious();
        }
        return null;
    }

    /**
     * Previous exception getter
     * Does not override PHP 5.3+ getPrevious method
     *
     * @return Exception|null
     */
    public function _getPrevious()
    {
        return $this->_previous;
    }

    /**
     * String representation of the exception
     *
     * @return string
     */
    public function __toString()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $previous = $this->getPrevious();
            if (null !== $previous) {
                return $previous->__toString() .
                    '\n\nNext:\n' .
                    parent::__toString();

            }
        }
        return parent::__toString();
    }
}
