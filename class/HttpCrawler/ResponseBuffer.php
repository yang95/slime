<?php
namespace Slime\HttpCrawler;

class ResponseBuffer
{
    protected $mCBRespCreator = null;

    protected $_sStr      = '';
    protected $_niIndex   = null;
    protected $_niLen     = null;
    protected $_bFinished = false;

    /** @var string */
    protected $sProtocol;

    /** @var int */
    protected $iStatus;

    /** @var string */
    protected $sMSG;

    /** @var array */
    protected $aHeader;

    /** @var string */
    protected $sBody;

    public function setRespCreator($mCBRespCreator)
    {
        $this->mCBRespCreator = $mCBRespCreator;
    }

    public function addString($sStr)
    {
        $this->_sStr .= $sStr;
        $this->parse();
    }

    public function isFinished()
    {
        return $this->_bFinished;
    }

    public function generateRESP()
    {
        $aData = [
            'protocol' => $this->sProtocol,
            'status'   => $this->iStatus,
            'msg'      => $this->sMSG,
            'header'   => $this->aHeader,
            'body'     => $this->sBody
        ];
        return $this->mCBRespCreator === null ? $aData : call_user_func($this->mCBRespCreator, $aData);
    }

    protected function parse()
    {
        if ($this->isFinished()) {
            return true;
        }

        if ($this->_niIndex === null) {
            if (($iPos = strpos($this->_sStr, "\r\n\r\n")) === false) {
                return false;
            }
            $this->_niIndex = $iPos;
        }

        # parse header
        if (empty($this->aHeader)) {
            $sHeader = substr($this->_sStr, 0, $this->_niIndex);
            $aHeader = explode("\r\n", $sHeader);
            do {
                $sFirst = trim(array_shift($aHeader));
                if ($sFirst !== '') {
                    list($sProtocol, $sStatus, $sMSG) = explode(' ', $sFirst);
                    $this->sProtocol = $sProtocol;
                    $this->iStatus   = (int)$sStatus;
                    $this->sMSG      = $sMSG;
                }
            } while (count($aHeader) > 0 && $sFirst === '');

            $aTidyHeader = [];
            foreach ($aHeader as $sLine) {
                if (trim($sLine) === '') {
                    continue;
                }
                list($sK, $sV) = explode(":", $sLine, 2);
                $aTidyHeader[trim($sK)][] = trim($sV);
            }

            if (isset($aTidyHeader['Content-Length'])) {
                $this->_niLen = (int)$aTidyHeader['Content-Length'][0];
            }
            $this->_niIndex += 4;
        }

        # parse body
        if ($this->_niLen !== null) {
            $this->sBody .= substr($this->_sStr, $this->_niIndex);
            $iBodySize      = strlen($this->sBody);
            $this->_niIndex = $iBodySize;
            if ($iBodySize === $this->_niLen) {
                $this->_bFinished = true;
                return true;
            } else {
                return false;
            }
        } else {
            //@todo
            throw new \RuntimeException('to do trunked');
            var_dump($this->_sStr);
            exit;
        }
    }
}