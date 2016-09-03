<?php
namespace Slime\Utils;

class Str
{
    public static function buildFromTPL($sTPL, $aReplaceMap)
    {
        $aReplace = [];
        foreach ($aReplaceMap as $sK => $mV) {
            $aReplace['{' . $sK . '}'] = (is_array($mV) || is_object($mV)) ? json_encode($mV) : (string)$mV;
        }

        return strtr($sTPL, $aReplace);
    }
}
