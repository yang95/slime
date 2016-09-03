<?php
namespace Slime\RDBMS\ORM\Engine;

use Slime\Container\ContainerObject;
use SlimeInterface\Event\EventInterface;

/**
 * Class PDO
 *
 * @package Slime\RDBMS\ORM\Engine
 */
class PDO extends ContainerObject
{
    protected $aPDOConfig = [];
    private   $nInst;

    public function __construct($aPDOConfig)
    {
        $this->aPDOConfig = $aPDOConfig;
    }

    public function __call($sMethod, $aArgv)
    {
        /** @var null|EventInterface $nEV */
        $nEV = $this->_getIfExist('Event');
        $PDO = $this->getOriginPDO();

        if ($nEV !== null) {
            $Local = new \ArrayObject();
            $nEV->fire(RDBEvent::EV_BEFORE_RUN, [$sMethod, $aArgv, $Local]);
            if (!isset($Local['__RESULT__'])) {
                $mRS = call_user_func_array([$PDO, $sMethod], $aArgv);
                $Local['__RESULT__'] = $mRS;
            }
            $nEV->fire(RDBEvent::EV_BEFORE_RUN, [$sMethod, $aArgv, $Local]);
            $mRS = $Local['__RESULT__'];
        } else {
            $mRS = call_user_func_array([$PDO, $sMethod], $aArgv);
        }
        return $mRS;
    }

    /**
     * @return \PDO
     */
    public function getOriginPDO()
    {
        if ($this->nInst === null) {
            $this->nInst = new \PDO(
                $this->aPDOConfig['dsn'],
                $this->aPDOConfig['username'],
                $this->aPDOConfig['passwd'],
                $this->aPDOConfig['options']
            );
        }

        return $this->nInst;
    }
}
