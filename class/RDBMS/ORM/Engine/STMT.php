<?php
namespace Slime\RDBMS\ORM\Engine;

use Slime\Container\ContainerObject;
use SlimeInterface\Event\EventInterface;

class STMT extends ContainerObject
{
    /** @var \PDOStatement */
    protected $STMT;

    public function __construct(\PDOStatement $STMT)
    {
        $this->STMT = $STMT;
    }

    public function __call($sMethod, array $aArgv = [])
    {
        /** @var null|EventInterface $nEV */
        $nEV = $this->_getIfExist('Event');
        $STMT = $this->STMT;
        if ($nEV !== null) {
            $Local = new \ArrayObject();
            $nEV->fire(RDBEvent::EV_BEFORE_STMT_RUN, [$sMethod, $aArgv, $Local]);
            if (!isset($Local['__RESULT__'])) {
                $mRS = call_user_func_array([$STMT, $sMethod], $aArgv);
                $Local['__RESULT__'] = $mRS;
            }
            $nEV->fire(RDBEvent::EV_AFTER_STMT_RUN, [$sMethod, $aArgv, $Local]);
            $mRS = $Local['__RESULT__'];
        } else {
            $mRS = call_user_func_array([$STMT, $sMethod], $aArgv);
        }

        return $mRS;
    }
}