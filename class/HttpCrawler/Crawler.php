<?php
namespace Slime\HttpCrawler;

use Psr\Http\Message\RequestInterface;

class Crawler
{
    protected $aREQ = [];

    protected $aWaitForConn = [];

    protected $aWaitForWrite = [];

    protected $aWaitForRead = [];

    protected $aUnFinishedResponse = [];

    protected $aFinishedResponse = [];

    protected $aMapFDReq = [];

    protected $aFindMapResponse = [];

    public function addRequest(RequestInterface $REQ, $mCB)
    {
        $sID                  = spl_object_hash($REQ);
        $this->aREQ[$sID]     = [$REQ, $mCB, 0];
        $this->aWaitForConn[] = $sID;
    }

    public function run()
    {
        do {
            # conn
            $aConnFailed = [];
            do {
                $sID = array_pop($this->aWaitForConn);
                if (!isset($this->aREQ[$sID])) {
                    continue;
                }
                /** @var RequestInterface $REQ */
                list($REQ, $mCB) = $this->aREQ[$sID];

                $sHost = $REQ->getHeaderLine('Host');
                if (strpos($sHost, ':') === false) {
                    $sHost .= ':80';
                }
                $rFD = stream_socket_client('tcp://' . $sHost, $iErr, $sErr, 1);
                if ($rFD === false) {
                    $aConnFailed[] = $sID;
                } else {
                    stream_set_blocking($rFD, false);
                    $this->aWaitForWrite[]      = $rFD;
                    $this->aMapFDReq[(int)$rFD] = ['request' => $REQ, 'cb' => $mCB];
                }
            } while (count($this->aWaitForConn) > 0);
            $this->aWaitForConn = array_merge($this->aWaitForConn, $aConnFailed);

            # write
            if (count($this->aWaitForWrite) > 0) {
                $aRead  = $aExcept = [];
                $aWrite = $this->aWaitForWrite;
                if (!stream_select($aRead, $aWrite, $aExcept, 0, 10000)) {
                    continue;
                }
                $aWriteFailed = [];
                do {
                    $rFD = array_shift($aWrite);
                    if (fwrite($rFD, (string)$REQ)) {
                        $this->aWaitForRead[] = $rFD;
                    } else {
                        $aWriteFailed[] = $rFD;
                    }
                } while (count($aWrite) > 0);
                $this->aWaitForWrite = array_merge($aWrite, $aWriteFailed);
            }

            # read
            if (count($this->aWaitForRead) > 0) {
                $aWrite = $aExcept = [];
                $aRead  = $this->aWaitForRead;
                if (!stream_select($aRead, $aWrite, $aExcept, 0, 10000)) {
                    continue;
                }
                do {
                    $rFD = array_shift($aRead);
                    $iFD = (int)$rFD;
                    if (!isset($this->aMapFDReq[$iFD]['response'])) {
                        $RB                                = new ResponseBuffer();
                        $this->aMapFDReq[$iFD]['response'] = $RB;
                    } else {
                        $RB = $this->aMapFDReq[$iFD]['response'];
                    }
                    while (!feof($rFD)) {
                        $bsData = fread($rFD, 10240);
                        if ($bsData === false) {
                            break;
                        }
                        $RB->addString($bsData);
                    }
                    if ($RB->isFinished()) {
                        $this->aFinishedResponse[] = $rFD;
                    } else {
                        $this->aUnFinishedResponse[] = $rFD;
                        $aRead[]                     = $rFD;
                    }
                } while (count($aRead) > 0);
            }

            # cb
            if (count($this->aFinishedResponse) > 0) {
                do {
                    $rFD = array_pop($this->aFinishedResponse);
                    $iFD = (int)$rFD;
                    if (!isset($this->aMapFDReq[$iFD])) {
                        continue;
                    }
                    $aOne = $this->aMapFDReq[$iFD];
                    call_user_func($aOne['cb'], $aOne['request'], $aOne['response'], $this);
                    unset($this->aMapFDReq[$iFD]);
                } while (count($this->aFinishedResponse) > 0);
            }
        } while (
            count($this->aWaitForConn) > 0 ||
            count($this->aWaitForWrite) > 0 ||
            count($this->aWaitForRead) > 0
        );
    }
}