<?php
namespace Slime\Log;

/**
 * Class Writer_STDFD
 *
 * @package Slime\Log
 * @author  smallslime@gmail.com
 */
class Writer_STDFD extends Writer_ABS
{
    protected $sFormat;

    public function __construct($nsFormat = null)
    {
        $this->sFormat = $nsFormat === null ? '[{iLevel}] : {sTime} ; {sMessage}' : $nsFormat;
    }

    public function acceptData($aRow)
    {
        echo str_replace(
                ['{sTime}', '{iLevel}', '{sMessage}', '{sGuid}'],
                [$aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']],
                $this->sFormat
            ) . PHP_EOL;

        /*
        if ($aRow['iLevel'] <= Logger::LEVEL_INFO) {
            file_put_contents('php://stdout', $sStr);
        } else {
            file_put_contents('php://stderr', $sStr);
        }
        */
    }
}