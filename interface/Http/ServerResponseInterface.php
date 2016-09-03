<?php
namespace SlimeInterface\Http;

use Psr\Http\Message\ResponseInterface;

interface ServerResponseInterface extends ResponseInterface
{
    /**
     * @return void
     */
    public function send();

    /**
     * @param int    $iStatus
     * @param string $sReasonPhrase
     *
     * @return self
     */
    public function setStatus($iStatus, $sReasonPhrase = '');

    /**
     * @param string $sKey
     * @param string $sValue
     * @param bool   $bOverwrite
     *
     * @return self
     */
    public function setHeader($sKey, $sValue, $bOverwrite = true);

    /**
     * @param string $sKey
     * @param string $sValue
     *
     * @return self
     */
    public function setAddHeader($sKey, $sValue);

    /**
     * @param string      $sName
     * @param null|string $nsValue
     * @param null|int    $niExpire
     * @param null|string $nsPath
     * @param null|string $nsDomain
     * @param null|bool   $nbSecure
     * @param null|bool   $nbHttpOnly
     *
     * @return self
     */
    public function setCookie(
        $sName,
        $nsValue = null,
        $niExpire = null,
        $nsPath = null,
        $nsDomain = null,
        $nbSecure = null,
        $nbHttpOnly = null
    );

    /**
     * @param string $sStr
     *
     * @return self
     */
    public function setBody($sStr);
}
