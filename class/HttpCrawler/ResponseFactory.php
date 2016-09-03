<?php
namespace Slime\HttpCrawler;

use Slime\Http\Response;

class ResponseFactory
{
    public static function create($sStr)
    {
        $nRESP  = null;
        $aBlock = explode("\r\n\r\n", $sStr, 2);
        if (count($aBlock) < 2) {
            goto END;
        }
        list($sHeader, $sBody) = $aBlock;
        $aHeader = explode("\r\n", $sHeader);
        $aFirst  = [];
        do {
            $nsRow = array_shift($aHeader);
            if ($nsRow === null) {
                break;
            }
            if (trim($nsRow) === '') {
                continue;
            }
            $aFirst = explode(' ', $nsRow);
            break;
        } while (true);
        if (count($aFirst) === 0) {
            goto END;
        }
        $aTidyHeader = [];
        foreach ($aHeader as $sRow) {
            list($sK, $sV) = explode(':', $sRow, 2);
            $aTidyHeader[$sK][] = $sV;
        }
        $nRESP = new Response('php://memory', $aFirst[1], $aTidyHeader);
        $nRESP->getBody()->write($sBody);

        END:
        return $nRESP;
    }
}