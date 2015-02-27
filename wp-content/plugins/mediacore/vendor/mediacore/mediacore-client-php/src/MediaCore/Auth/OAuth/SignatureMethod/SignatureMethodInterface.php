<?php
namespace MediaCore\Auth\OAuth\SignatureMethod;


/**
 * A signature method interface
 *
 * @category    MediaCore
 * @package     MediaCore\Auth\OAuth\SignatureMethod\SignatureMethodInterface
 * @subpackage
 * @copyright   Copyright (c) 2014 MediaCore Technologies Inc.
 *              (http://www.mediacore.com)
 * @license
 * @version     Release:
 * @link        https://github.com/mediacore/mediacore-client-php
 */
interface SignatureMethodInterface
{
    /**
     * Get the signature name
     *
     * @return string
     */
    public function getName();

    /**
     * Build the Signature
     *
     * @param Consumer $consumer
     * @param string $baseString
     * @return string
     */
    public function buildSignature($consumer, $baseString);
}
