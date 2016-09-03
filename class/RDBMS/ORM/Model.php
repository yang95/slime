<?php
namespace Slime\RDBMS\ORM;

use Slime\Container\ContainerObject;
use SlimeInterface\RDBMS\ORM\CollectionInterface;
use SlimeInterface\RDBMS\ORM\Engine\EnginePoolInterface;
use SlimeInterface\RDBMS\ORM\ModelFactoryInterface;
use SlimeInterface\RDBMS\ORM\ModelInterface;
use SlimeInterface\RDBMS\SQL\SQLInterface;
use SlimeInterface\RDBMS\SQL\DeleteInterface;
use SlimeInterface\RDBMS\SQL\InsertInterface;
use SlimeInterface\RDBMS\SQL\SelectInterface;
use SlimeInterface\RDBMS\SQL\UpdateInterface;

use Slime\RDBMS\SQL\Condition;
use Slime\RDBMS\SQL\SQLFactory;
use Slime\RDBMS\ORM\Engine\PDO;

/**
 * Class Model
 *
 * @package Slime\RDBMS\SQL
 */
class Model extends ContainerObject implements ModelInterface
{
    /** @var SQLFactory */
    protected $SQLFactory;

    /** @var int */
    protected $iSQLType = SQLFactory::TYPE_MYSQL;

    /** @var string */
    protected $sTable;
    /** @var string */
    protected $sPK = 'id';
    /** @var string */
    protected $sFK;

    /** @var string */
    protected $sModelItem;

    /** @var string */
    protected $sEngineKey = 'default';

    /** @var EnginePoolInterface */
    protected $EnginePool;

    /** @var ModelFactoryInterface */
    protected $ModelFactory;

    public function __construct(
        $sModelName,
        array $aModelConfig,
        EnginePoolInterface $EnginePool,
        ModelFactoryInterface $ModelFactory
    ) {
        $this->EnginePool = $EnginePool;
        $this->SQLFactory = SQLFactory::create($this->iSQLType, $this);
        if (!$this->sTable) {
            $sFullClass   = get_called_class();
            $biPos        = strrpos($sFullClass, '\\');
            $this->sTable = $biPos === false ? $sFullClass : substr($sFullClass, $biPos + 1);
        }
        if (!$this->sFK) {
            $this->sFK = $this->sTable . '_id';
        }
        if (!$this->sModelItem) {
            $this->sModelItem =
                $aModelConfig['namespace'] . "\\" .
                $aModelConfig['item_pre'] . $sModelName . $aModelConfig['item_post'];
        }

        $this->ModelFactory = $ModelFactory;
    }

    /**
     * @return string
     */
    public function getPK()
    {
        return $this->sPK;
    }

    /**
     * @return string
     */
    public function getFK()
    {
        return $this->sFK;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->sTable;
    }

    /**
     * @param $mPK
     *
     * @return Item|null
     */
    public function findByPK($mPK)
    {
        return count(
            $Col = $this->SQLFactory->select()
                ->table($this->sTable)
                ->where(Condition::asAnd()->eq($this->sPK, $mPK))
                ->limit(1)
                ->run()
        ) === 0 ? null : $Col->current();
    }

    /**
     * @return Collection
     */
    public function findAll()
    {
        return $this->SQLFactory->select()->table($this->sTable)->run();
    }

    /**
     * @return Item
     */
    public function createEmptyItem()
    {
        $Item = new Item([], $this, null);
        $Item->__init__($this->_getContainer());
        return $Item;
    }

    /**
     * @return SelectInterface
     */
    public function select()
    {
        return $this->SQLFactory->select()->table($this->sTable);
    }

    /**
     * @return UpdateInterface
     */
    public function update()
    {
        return $this->SQLFactory->update()->table($this->sTable);
    }

    /**
     * @return InsertInterface
     */
    public function insert()
    {
        return $this->SQLFactory->insert()->table($this->sTable);
    }

    /**
     * @return DeleteInterface
     */
    public function delete()
    {
        return $this->SQLFactory->delete()->table($this->sTable);
    }

    /**
     * @param SQLInterface $SQL
     *
     * @return null|int|CollectionInterface|string
     *              null: no execute
     *              int: update/delete(effect rows);
     *              string: insert(last insert id);
     *              Collection: select
     */
    public function run(SQLInterface $SQL)
    {
        return $this->_runWithPDO($this->EnginePool->getInst($this->sEngineKey, $this, $SQL), $SQL);
    }

    /**
     * @param null|string $nsEngineKey
     *
     * @return mixed
     */
    public function getEngine($nsEngineKey = null)
    {
        return $this->EnginePool->getInst(
            $nsEngineKey === null ? $this->sEngineKey : $nsEngineKey,
            $this, ''
        );
    }

    /**
     * @param PDO|\PDO     $PDO
     * @param SQLInterface $SQL
     *
     * @return int|Collection
     */
    protected function _runWithPDO(PDO $PDO, SQLInterface $SQL)
    {
        static $aMapP = [
            0         => \PDO::PARAM_STR,
            'string'  => \PDO::PARAM_STR,
            'boolean' => \PDO::PARAM_BOOL,
            'integer' => \PDO::PARAM_INT,
            'null'    => \PDO::PARAM_NULL
        ];
        $naBind = $SQL->getBind();
        switch ($iType = $SQL->getSQLType()) {
            case SQLInterface::SQL_TYPE_SELECT:
                if ($naBind === null) {
                    $mSTMT = $PDO->query((string)$SQL);
                    $aRS   = $mSTMT === false ? [] : $mSTMT->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    $mSTMT = $PDO->prepare((string)$SQL);
                    if ($mSTMT) {
                        foreach ($naBind as $sK => $mOne) {
                            $sType = gettype($mOne);
                            $mSTMT->bindValue($sK, $mOne, isset($aMapP[$sType]) ? $aMapP[$sType] : $aMapP[0]);
                        }
                    }
                    $aRS = $mSTMT->execute() ? $mSTMT->fetchAll(\PDO::FETCH_ASSOC) : [];
                }
                $Obj = new Collection(
                    $aRS,
                    $this->sModelItem,
                    $this
                );
                $Obj->__init__($this->_getContainer());
                $Obj->buildData();
                return $Obj;
            case SQLInterface::SQL_TYPE_INSERT:
            case SQLInterface::SQL_TYPE_UPDATE:
            case SQLInterface::SQL_TYPE_DELETE:
                if ($naBind === null) {
                    $iEffectRows = $PDO->exec((string)$SQL);
                } else {
                    $mSTMT = $PDO->prepare((string)$SQL);
                    if ($mSTMT) {
                        foreach ($naBind as $sK => $mOne) {
                            $sType = gettype($mOne);
                            $mSTMT->bindValue($sK, $mOne, isset($aMapP[$sType]) ? $aMapP[$sType] : $aMapP[0]);
                        }
                        $iEffectRows = $mSTMT->execute();
                    } else {
                        $iEffectRows = 0;
                    }
                }
                return $iType === SQLInterface::SQL_TYPE_INSERT ?
                    ($iEffectRows === 0 ? false : $PDO->lastInsertId()) :
                    $iEffectRows;
            default:
                throw new \InvalidArgumentException();
        }
    }

    /**
     * @param string $sModelName
     *
     * @return ModelInterface
     */
    public function getOtherModel($sModelName)
    {
        return $this->ModelFactory->getModel($sModelName);
    }

    /**
     * @param mixed $mCB
     *
     * @return int
     */
    public function transaction($mCB)
    {
        /** @var \PDO|PDO $PDO */
        $PDO = $this->getEngine();
        $PDO->query('begin');
        try {
            $iErr = call_user_func($mCB, $this);
            if ($iErr !== 0) {
                $PDO->query('rollback');
            }
            $PDO->query('commit');
        } catch (\PDOException $E) {
            $iErr = -1;
            var_dump($E->getMessage());
            $PDO->query('rollback');
        }
        return $iErr;
    }
}