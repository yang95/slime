<?php
namespace Slime\Log;

/**
 * Class Writer_File
 *
 * @package Slime\Log
 * @author  smallslime@gmail.com
 */
class Writer_File extends Writer_ABS
{
    protected $aBuf = [];
    protected $iBuf = 0;
    protected $iBufMax;
    protected $sFileFormat;
    protected $sContentFormat;
    protected $aVarMap;
    protected $aLevelMap;

    public function __construct(
        $sFileFormat,
        $iBufMax = 0,
        $nsContentFormat = null,
        $naVarMap = null,
        $naLevelMap = null
    ) {
        $this->iBufMax        = $iBufMax;
        $this->sFileFormat    = $sFileFormat;
        $this->sContentFormat = $nsContentFormat === null ? '[{iLevel}] : {sTime} ; {sGuid} ; {sMessage}' : (string)$nsContentFormat;
        $this->aVarMap        = $naVarMap === null ? [
            '{date}' => function () {
                return date('Y-m-d');
            }
        ] : (array)$naVarMap;
        $this->aLevelMap      = $naLevelMap === null ? [
            Logger::LEVEL_DEBUG => 'access',
            Logger::LEVEL_INFO  => 'access',
            -1                  => 'error'
        ] : (array)$naLevelMap;
    }

    public function acceptData($aRow)
    {
        $aVarMap = [];
        if (!isset($this->aVarMap['{level}'])) {
            $aVarMap['{level}'] = isset($this->aLevelMap[$aRow['iLevel']]) ?
                $this->aLevelMap[$aRow['iLevel']] :
                $this->aLevelMap[-1];
        }

        $aVarMap = array_merge($aVarMap, $this->aVarMap);
        foreach ($aVarMap as $sK => $mV) {
            if (is_callable($mV)) {
                $aVarMap[$sK] = call_user_func($mV);
            }
        }
        $sFilePath = str_replace(array_keys($aVarMap), array_values($aVarMap), $this->sFileFormat);

        $sStr = str_replace(
                ['{sTime}', '{iLevel}', '{sMessage}', '{sGuid}'],
                [$aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']],
                $this->sContentFormat
            ) . PHP_EOL;

        if ($this->iBufMax > 0) {
            if ($this->iBuf >= $this->iBufMax) {
                $this->_flush();
            } else {
                $this->aBuf[$sFilePath][] = $sStr;
                $this->iBuf++;
            }
        } else {
            if (!file_put_contents($sFilePath, $sStr, FILE_APPEND | LOCK_EX)) {
                trigger_error("write file[$sFilePath] failed", E_USER_WARNING);
            }
        }
    }

    protected function _flush()
    {
        foreach ($this->aBuf as $sFilePath => $aBufData) {
            if (!empty($aBufData)) {
                if (!file_put_contents($sFilePath, implode('', $aBufData), FILE_APPEND | LOCK_EX)) {
                    trigger_error("write file[$sFilePath] failed", E_USER_WARNING);
                }

                $this->aBuf[$sFilePath] = [];
            }
        }
        $this->iBuf = 0;
    }

    public function __destruct()
    {
        $this->_flush();
    }
}
