<?php
namespace Slime\Container;

use SlimeInterface\Container\ContainerInterface;

class ContainerObject
{
    /**
     * @var ContainerInterface
     */
    private $__Container;

    public function __call($sName, $aArgv)
    {
        switch (substr($sName, 0, 4)) {
            case '_get':
                return $this->_getForce(substr($sName, 4));
            default:
                throw new \BadMethodCallException();
        }
    }

    public function __init__(ContainerInterface $Container)
    {
        $this->__Container = $Container;
    }

    public function _getContainer()
    {
        return $this->__Container;
    }

    public function _getForce($sName)
    {
        return $this->__Container->get($sName);
    }

    public function _getIfExist($sName)
    {
        return $this->__Container->has($sName) ? $this->__Container->get($sName) : null;
    }

    public function __sleep()
    {
        $Ref = new \ReflectionObject($this);
        $aProp = $Ref->getDefaultProperties();
        unset($aProp['__Container']);
        $aPropKey = array_keys($aProp);
        return array_combine($aPropKey, $aPropKey);
    }
}