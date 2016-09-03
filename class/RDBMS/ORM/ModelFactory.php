<?php
namespace Slime\RDBMS\ORM;

use Slime\Container\ContainerObject;
use SlimeInterface\Container\ContainerInterface;
use SlimeInterface\RDBMS\ORM\Engine\EnginePoolInterface;
use SlimeInterface\RDBMS\ORM\ModelFactoryInterface;
use SlimeInterface\RDBMS\ORM\ModelInterface;

/**
 * Class ModelFactory
 *
 * @package Slime\RDBMS\ORM
 *
 * @method ContainerInterface _getContainer() _getContainer()
 */
class ModelFactory extends ContainerObject implements ModelFactoryInterface
{
    /** @var ModelInterface[] */
    protected $aModelInst;

    /** @var array */
    protected $aModelConf;

    /** @var array */
    protected $aEngineConf;

    /** @var null|EnginePoolInterface */
    protected $nEnginePool;

    /**
     * ModelFactory constructor.
     *
     * @param array $aModelConf
     * @param array $aEngineConf
     */
    public function __construct(array $aModelConf, array $aEngineConf)
    {
        $this->aModelConf  = $aModelConf;
        $this->aEngineConf = $aEngineConf;
    }

    public function __get($sName)
    {
        return $this->getModel($sName);
    }

    /**
     * @param string $sModelName
     *
     * @return ModelInterface
     */
    public function getModel($sModelName)
    {
        if (!isset($this->aModelInst[$sModelName])) {
            $sModelClassName =
                $this->aModelConf['namespace'] . "\\" .
                $this->aModelConf['model_pre'] . $sModelName . $this->aModelConf['model_post'];

            $Obj = new $sModelClassName(
                $sModelName,
                $this->aModelConf,
                $this->getEnginePool(),
                $this
            );
            if ($Obj instanceof Model) {
                $Obj->__init__($this->_getContainer());
            }
            $this->aModelInst[$sModelName] = $Obj;
        }

        return $this->aModelInst[$sModelName];
    }

    /**
     * @return EnginePoolInterface
     */
    protected function getEnginePool()
    {
        if ($this->nEnginePool === null) {
            $Obj = new $this->aEngineConf['engine_pool_class']($this->aEngineConf['engine_pool_config']);
            if (!$Obj instanceof EnginePoolInterface) {
                throw new \RuntimeException();
            }
            if ($Obj instanceof ContainerObject) {
                $Obj->__init__($this->_getContainer());
            }
            $this->nEnginePool = $Obj;
        }
        return $this->nEnginePool;
    }
}