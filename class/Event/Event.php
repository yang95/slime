<?php
namespace Slime\Event;

use Slime\Container\ContainerObject;
use SlimeInterface\Event\EventInterface;

/**
 * Class Event
 *
 * @package Slime\Event
 * @author  smallslime@gmail.com
 */
class Event extends ContainerObject implements EventInterface
{
    protected $aListener       = [];
    protected $aSortedListener = [];

    /**
     * @param string $sName
     * @param array  $aArgv
     *
     * @return bool
     */
    public function fire($sName, array $aArgv = [])
    {
        if (!empty($this->aListener[$sName])) {
            $aArgv[] = $this;
            foreach ($this->getSortedListeners($sName) as $mCB) {
                if (call_user_func_array($mCB, $aArgv) === false) {
                    break;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array|string $asName
     * @param mixed        $mCB
     * @param int          $iPriority
     * @param null|string  $nsSign
     *
     * @return self
     */
    public function listen($asName, $mCB, $iPriority = 0, $nsSign = null)
    {
        foreach ((array)$asName as $sName) {
            if (!empty($this->aSortedListener[$sName])) {
                $this->aSortedListener[$sName] = [];
            }
            if ($nsSign === null) {
                $this->aListener[$sName][$iPriority][] = $mCB;
            } else {
                if (isset($this->aListener[$sName][$iPriority][$nsSign])) {
                    throw new \RuntimeException("[EVENT] ; Event[$sName.$nsSign] has exist");
                }
                $this->aListener[$sName][$iPriority][$nsSign] = $mCB;
            }
        }

        return $this;
    }

    /**
     * @param string $sName
     *
     * @return bool
     */
    public function hasListener($sName)
    {
        return isset($this->aListener[$sName]);
    }

    /**
     * @param string      $sName
     * @param null|string $nsSign
     *
     * @return self
     */
    public function forget($sName, $nsSign = null)
    {
        if ($nsSign === null) {
            if (isset($this->aListener[$sName])) {
                unset($this->aListener[$sName]);
            }
        } else {
            if (isset($this->aListener[$sName][$nsSign])) {
                unset($this->aListener[$sName][$nsSign]);
            }
        }

        return $this;
    }

    /**
     * @param string $sName
     *
     * @return array
     */
    public function getSortedListeners($sName)
    {
        if (empty($this->aSortedListener[$sName])) {
            krsort($this->aListener[$sName]);
            $this->aSortedListener[$sName] = call_user_func_array('array_merge', $this->aListener[$sName]);
        }

        return $this->aSortedListener[$sName];
    }
}
