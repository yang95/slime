<?php
namespace Slime\Http;

use SlimeInterface\Http\ServerResponseInterface;

class ServerResponse extends Response implements ServerResponseInterface
{
    public static function create($sBodyStream = 'php://memory', $iStatus = 200, array $aHeader = [])
    {
        return new self($sBodyStream, $iStatus, $aHeader);
    }

    /** @var array */
    protected $aCookie = [];

    /**
     * @return void
     */
    public function send()
    {
        if (($iStatus = $this->getStatusCode()) !== 200) {
            header(sprintf(
                'HTTP/%s %d%s',
                $this->getProtocolVersion(),
                $iStatus,
                ($sReason = $this->getReasonPhrase()) === '' ? '' : " $sReason"
            ));
        }
        foreach ($this->aHeader as $sK => $aRow) {
            foreach ($aRow as $sOne) {
                header("$sK: $sOne", false);
            }
        }
        if (count($this->aCookie) > 0) {
            foreach ($this->aCookie as $aOne) {
                call_user_func_array('setcookie', $aOne);
            }
        }
        echo (string)$this->Body;
    }

    /**
     * @param int    $iStatusCode
     * @param string $sReasonPhrase
     *
     * @return self
     */
    public function setStatus($iStatusCode, $sReasonPhrase = '')
    {
        $this->iStatusCode   = $iStatusCode;
        $this->sReasonPhrase = $sReasonPhrase === '' ? $this->getReasonPhrase() : $sReasonPhrase;
        return $this;
    }

    /**
     * @param string $sKey
     * @param string $sValue
     * @param bool   $bOverwrite
     *
     * @return $this
     */
    public function setHeader($sKey, $sValue, $bOverwrite = true)
    {
        if ($bOverwrite || !isset($this->aHeader[$sKey])) {
            $this->aHeader[$sKey] = [$sValue];
        }

        return $this;
    }

    /**
     * @param string $sKey
     * @param string $sValue
     *
     * @return self
     */
    public function setAddHeader($sKey, $sValue)
    {
        $this->aHeader[$sKey][] = $sValue;

        return $this;
    }

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
    ) {
        $this->aCookie[] = [$sName, $nsValue, $niExpire, $nsPath, $nsDomain, $nbSecure, $nbHttpOnly];

        return $this;
    }

    /**
     * @param string $sStr
     *
     * @return self
     */
    public function setBody($sStr)
    {
        $this->Body->write($sStr);

        return $this;
    }
}
