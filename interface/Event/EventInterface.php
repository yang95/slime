<?php
namespace SlimeInterface\Event;

interface EventInterface
{
    /**
     * @param string $sName
     * @param array  $aArgv
     *
     * @return bool
     */
    public function fire($sName, array $aArgv = []);

    /**
     * @param array|string $asName
     * @param mixed        $mCB
     * @param int          $iPriority
     * @param null|string  $nsSign
     *
     * @return self
     */
    public function listen($asName, $mCB, $iPriority = 0, $nsSign = null);

    /**
     * @param string $sName
     *
     * @return bool
     */
    public function hasListener($sName);

    /**
     * @param string      $sName
     * @param null|string $nsSign
     *
     * @return self
     */
    public function forget($sName, $nsSign = null);
}