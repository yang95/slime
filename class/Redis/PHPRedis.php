<?php
namespace Slime\Redis;

use Slime\Container\ContainerObject;
use SlimeInterface\Event\EventInterface;

/**
 * Class PHPRedis
 *
 * @package Slime\Redis
 */
class PHPRedis extends ContainerObject
{
    protected static $aDefaultOptionConf = [
        'read_timeout' => 3,
    ];

    /** @var array */
    protected $aServerConf;

    /** @var array */
    protected $aOptionConf;

    /** @var null|\Memcached */
    private $nInst;

    public function __construct(array $aServer, array $aOption = [])
    {
        $this->aServerConf = $aServer;
        $this->aOptionConf = array_merge(self::$aDefaultOptionConf, $aOption);
    }

    public function __call($sMethod, $aArgv)
    {
        $Redis = $this->getInst();

        /** @var null|EventInterface $nEV */
        $nEV = $this->_getIfExist('Event');
        if ($nEV !== null) {
            $Local = new \ArrayObject();
            $nEV->fire(RedisEvent::EV_BEFORE_EXEC, [$sMethod, $aArgv, $Local]);
            if (!isset($Local['__RESULT__'])) {
                $mRS = call_user_func_array([$Redis, $sMethod], $aArgv);
                $Local['__RESULT__'] = $mRS;
            }
            $nEV->fire(RedisEvent::EV_AFTER_EXEC, [$sMethod, $aArgv, $Local]);
            $mRS = $Local['__RESULT__'];
        } else {
            $mRS = call_user_func_array([$Redis, $sMethod], $aArgv);
        }
        return $mRS;
    }

    /**
     * @return \Redis
     */
    protected function getInst()
    {
        if ($this->nInst === null) {
            $Redis = new \Redis();
            $Redis->connect((string)$this->aServerConf['host'], (int)$this->aServerConf['port'], $this->aServerConf['timeout']);
            $Redis->setOption(\Redis::OPT_READ_TIMEOUT, $this->aOptionConf['read_timeout']);
            if (isset($this->aOptionConf['auth'])) {
                $Redis->auth($this->aOptionConf['auth']);
            }
            if (isset($this->aOptionConf['db'])) {
                $Redis->select($this->aOptionConf['db']);
            }
            $this->nInst = $Redis;
        }

        return $this->nInst;
    }
}