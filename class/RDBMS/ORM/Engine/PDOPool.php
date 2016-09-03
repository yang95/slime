<?php
namespace Slime\RDBMS\ORM\Engine;

use Slime\Container\ContainerObject;
use SlimeInterface\RDBMS\ORM\Engine\EnginePoolInterface;
use SlimeInterface\RDBMS\ORM\ModelInterface;
use SlimeInterface\RDBMS\SQL\SQLInterface;

class PDOPool extends ContainerObject implements EnginePoolInterface
{
    /** @var array */
    protected $aConf;

    /** @var array */
    protected $aPDO;

    public function __construct(array $aConf)
    {
        $this->aConf = $aConf;
    }

    /**
     * @param string              $sExpectKey
     * @param ModelInterface      $Model
     * @param SQLInterface|string $m_sSQL_SQL
     *
     * @return mixed
     */
    public function getInst($sExpectKey, ModelInterface $Model, $m_sSQL_SQL)
    {
        if (!isset($this->aPDO[$sExpectKey])) {
            $PDO = new PDO($this->aConf[$sExpectKey]);
            if ($PDO instanceof ContainerObject) {
                $PDO->__init__($this->_getContainer());
            }
            $this->aPDO[$sExpectKey] = $PDO;
        }
        return $this->aPDO[$sExpectKey];
    }
}