<?php
namespace Slime\HttpCrawler;

use Psr\Http\Message\RequestInterface;

class CurlCrawler
{
    protected $aToDoTask      = [];
    protected $aDoingTask     = [];
    protected $aFinishingTask = [];
    protected $aFinishedTask  = [];

    protected $aBufferTask = [];

    protected $mSuccCB   = null;
    protected $mFailedCB = null;

    public function __construct($iMaxConcurrency = 1000, $iDefaultTimeout = 30, $iSleepUSWhenWait = 10)
    {
        $this->iMaxConcurrency  = $iMaxConcurrency;
        $this->iDefaultTimeout  = $iDefaultTimeout;
        $this->iSleepUSWhenWait = $iSleepUSWhenWait;
    }

    public function addTask(RequestInterface $REQ, $mSuccCB = null, $mFailedCB = null, $niTimeout = null)
    {
        $aTask = [
            'request'   => $REQ,
            'succ_cb'   => $mSuccCB,
            'failed_cb' => $mFailedCB,
            'timeout'   => $niTimeout
        ];
        if ($this->getCurrentTaskCount() >= $this->iMaxConcurrency) {
            $this->aBufferTask[] = $aTask;
        } else {
            $this->aToDoTask[] = $aTask;
        }

        return $this;
    }

    protected function getCurrentTaskCount()
    {
        return count($this->aToDoTask) +
        count($this->aDoingTask) +
        count($this->aFinishedTask) +
        count($this->aFinishedTask);
    }

    public function setDefaultCB($mSuccCB, $mFailedCB)
    {
        $this->mSuccCB   = $mSuccCB;
        $this->mFailedCB = $mFailedCB;
        return $this;
    }

    public function run()
    {
        $aFailed = [];
        do {
            # init
            if (count($this->aToDoTask) > 0) {
                foreach ($this->aToDoTask as $iK => $aOne) {
                    unset($this->aToDoTask[$iK]);
                    /** @var RequestInterface $REQ */
                    $REQ   = $aOne['request'];
                    $rCurl = curl_init();
                    curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, 1);
                    self::curl_setting_with_req($rCurl, $REQ);
                    $rMCurl = curl_multi_init();
                    curl_multi_add_handle($rMCurl, $rCurl);
                    curl_multi_exec($rMCurl, $iActive);
                    $aOne['curl']       = $rCurl;
                    $aOne['m_curl']     = $rMCurl;
                    $aOne['start_at']   = time();
                    $this->aDoingTask[] = $aOne;
                }
            }

            # doing
            if (count($this->aDoingTask) > 0) {
                foreach ($this->aDoingTask as $iK => $aOne) {
                    $iTimeout = $aOne['timeout'] === null ? $this->iDefaultTimeout : $aOne['timeout'];
                    if (time() - $aOne['start_at'] > $iTimeout) {
                        unset($this->aDoingTask[$iK]);
                        $mCB = isset($aOne['failed_cb']) ? $aOne['failed_cb'] : $this->mFailedCB;
                        call_user_func($mCB, $aOne['request'], $aOne, $this);
                        $aFailed[] = $aOne;
                    }
                    $iStillRunning = null;
                    curl_multi_exec($aOne['m_curl'], $iStillRunning);
                    if (!$iStillRunning) {
                        $aOne                   = $this->aDoingTask[$iK];
                        $this->aFinishingTask[] = $aOne;
                        unset($this->aDoingTask[$iK]);
                    }
                }
            }

            # reading
            if (count($this->aFinishingTask) > 0) {
                foreach ($this->aFinishingTask as $iK => $aOne) {
                    $sResponse = curl_multi_getcontent($aOne['curl']);
                    curl_close($aOne['curl']);
                    curl_multi_close($aOne['m_curl']);
                    $aOne['response']      = ResponseFactory::create($sResponse);
                    $this->aFinishedTask[] = $aOne;
                    unset($this->aFinishingTask[$iK]);
                }
            }

            # cb
            if (count($this->aFinishedTask) > 0) {
                foreach ($this->aFinishedTask as $iK => $aOne) {
                    $aOne = array_shift($this->aFinishedTask);
                    $mCB  = $aOne['succ_cb'] === null ? $this->mSuccCB : $aOne['succ_cb'];
                    call_user_func($mCB, $aOne['request'], $aOne['response'], $this);
                    unset($this->aFinishedTask[$iK]);
                }
            }

            NEXT:
            if (count($this->aBufferTask) > 0 && (($iCurrentTaskCount = $this->getCurrentTaskCount()) < $this->iMaxConcurrency)) {
                $iCouldTodoCount = $this->iMaxConcurrency - $iCurrentTaskCount;
                $i               = 0;
                while (count($this->aBufferTask) > 0) {
                    $this->aToDoTask[] = array_shift($this->aBufferTask);
                    if (++$i >= $iCouldTodoCount) {
                        break;
                    }
                }
            }
            usleep($this->iSleepUSWhenWait);
        } while (
            count($this->aToDoTask) !== 0 ||
            count($this->aDoingTask) !== 0 ||
            count($this->aFinishingTask) !== 0 ||
            count($this->aFinishedTask) !== 0
        );

        return $aFailed;
    }

    /**
     * @param RequestInterface $REQ
     * @param int              $iErr
     * @param string           $sErr
     *
     * @return null|\Slime\Http\Response
     */
    public function doOneTask(RequestInterface $REQ, &$iErr, &$sErr)
    {
        $rCurl = curl_init();
        curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, 1);
        self::curl_setting_with_req($rCurl, $REQ);
        $bsResponse = curl_exec($rCurl);
        $iErr       = curl_errno($rCurl);
        $sErr       = curl_error($rCurl);
        return $bsResponse === false ? null : ResponseFactory::create($bsResponse);
    }

    public static function curl_setting_with_req($rCurl, RequestInterface $REQ)
    {
        curl_setopt($rCurl, CURLOPT_HEADER, true);
        curl_setopt($rCurl, CURLOPT_URL, (string)$REQ->getUri());
        $aHeader = $REQ->getHeaders();
        if (count($aHeader) > 0) {
            $aTidyHeader = [];
            foreach ($aHeader as $sK => $aRow) {
                foreach ($aRow as $aOne) {
                    $aTidyHeader[] = "$sK: $aOne";
                }
            }
            curl_setopt($rCurl, CURLOPT_HTTPHEADER, $aTidyHeader);
        }
    }
}
