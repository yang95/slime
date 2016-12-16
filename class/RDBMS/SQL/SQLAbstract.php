<?php
namespace Slime\RDBMS\SQL;

use SlimeInterface\RDBMS\SQL\ConditionInterface;
use SlimeInterface\RDBMS\SQL\SelectInterface;
use SlimeInterface\RDBMS\SQL\SQLInterface;

abstract class SQLAbstract implements SQLInterface
{
    /** @var string|SQL_SELECT */
    protected $sTable_SQLSEL;

    /** @var Condition|null */
    protected $nWhere;

    /** @var array */
    protected $naJoin = [];

    /** @var int|null */
    protected $niLimit;

    /** @var int|null */
    protected $niOffset;

    /** @var array|null */
    protected $naOrder;

    /** @var string */
    protected $sQuote;

    /** @var null|object */
    protected $nModel;

    /** @var null|array */
    protected $naBindMap;

    /**
     * @param string      $sQuote
     * @param null|Object $nModel
     */
    public function __construct($sQuote, $nModel = null)
    {
        $this->sQuote = $sQuote;
        $this->nModel = $nModel;
    }

    /**
     * @param string $sTable
     *
     * @return self
     */
    public function table($sTable)
    {
        $this->sTable_SQLSEL = $sTable;

        return $this;
    }

    /**
     * @param ConditionInterface $Condition
     *
     * @return self
     */
    public function where(ConditionInterface $Condition)
    {
        $this->nWhere = $Condition;

        return $this;
    }

    /**
     * @param string|SelectInterface $sTable_SQLSEL
     * @param ConditionInterface     $Condition
     * @param string                 $sJoinType
     *
     * @return self
     */
    public function join($sTable_SQLSEL, ConditionInterface $Condition, $sJoinType = 'INNER')
    {
        $this->naJoin[] = [$sJoinType, $sTable_SQLSEL, $Condition];

        return $this;
    }

    /**
     * @param string $sOrder
     *
     * @return self
     */
    public function orderBy($sOrder)
    {
        $this->naOrder = $this->naOrder === null ? func_get_args() : array_merge($this->naOrder, func_get_args());

        return $this;
    }

    /**
     * @param int $iLimit
     *
     * @return self
     */
    public function limit($iLimit)
    {
        $this->niLimit = $iLimit;

        return $this;
    }

    /**
     * @param int $iOffset
     *
     * @return self
     */
    public function offset($iOffset)
    {
        $this->niOffset = $iOffset;

        return $this;
    }

    /**
     * @param array|object $m_a_Map
     *
     * @return self
     */
    public function bind($m_a_Map)
    {
        $aMap            = is_array($m_a_Map) ? $m_a_Map :
            (
            is_object($m_a_Map) && method_exists($m_a_Map, 'toArray') ?
                $m_a_Map->toArray() :
                (array)$m_a_Map
            );
        $this->naBindMap = $this->naBindMap === null ? $aMap : array_merge($this->naBindMap, $aMap);

        return $this;
    }

    /**
     * @return array|null
     */
    public function getBind()
    {
        return $this->naBindMap;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        return $this->nModel ? $this->nModel->run($this) : null;
    }

    /**
     * @param string|null $nsTable
     *
     * @return string
     */
    protected function parseTable($nsTable = null)
    {
        if ($this->sTable_SQLSEL instanceof SQL_SELECT) {
            $sParsedTable = '(' . (string)$this->sTable_SQLSEL . ')';
            if (($nsAlias = $this->sTable_SQLSEL->getAlias()) !== null) {
                $sParsedTable .= " AS {$this->sQuote}$nsAlias{$this->sQuote}";
            }
            return $sParsedTable;
        } else {
            return $nsTable === null ? (string)$this->sTable_SQLSEL : (string)$nsTable;
        }
    }

    /**
     * @param ConditionInterface $Condition
     *
     * @return string
     */
    protected function parseCondition($Condition)
    {
        $aData = $Condition->getData();
        $sRel  = $Condition->getRel();
        if (count($aData) === 0) {
            return '1';
        }
        $aRS = [];
        foreach ($aData as $mItem) {
            if ($mItem instanceof ConditionInterface) {
                $aRS[] = '(' . $this->parseCondition($mItem) . ')';
                continue;
            }

            // expr value
            $mV  = $mItem[2];
            $sOP = $mItem[1];

            if (empty($mV) && ($sOP == 'IN' || $sOP == 'NOT IN')) {
                if ($sOP == 'IN') {
                    $sRow = '0';
                } else {
                    $sRow = '1';
                }
            } else {
                if (is_array($mV)) {
                    // IN [1,2,3,4,5...]
                    $aTidy = [];
                    foreach ($mV as $mOne) {
                        $aTidy[] = is_string($mOne) ? "'$mOne'" : (string)$mOne;
                    }
                    $sStr = '(' . implode(',', $aTidy) . ')';
                } else {
                    $sStr = is_string($mV) ? "'{$mV}'" : $mV;
                }

                $sRow = sprintf(
                    '%s %s %s',
                    is_string($mItem[0]) && strpos($mItem[0], '.') === false ?
                        "{$this->sQuote}{$mItem[0]}{$this->sQuote}" : $mItem[0],
                    $sOP,
                    $sStr
                );
            }

            $aRS[] = $sRow;
        }

        return implode(" $sRel ", $aRS);
    }

    /**
     * @return null|string
     */
    protected function parseJoin()
    {
        if ($this->naJoin === null) {
            return null;
        }

        $aArr = [];
        foreach ($this->naJoin as $aRow) {
            $aArr[] = sprintf(
                "%s JOIN %s ON %s",
                $aRow[0],
                $this->parseTable($aRow[1]),
                $this->parseCondition($aRow[2])
            );
        }

        return implode(' ', $aArr);
    }

    /**
     * @return null|string
     */
    protected function parseOrder()
    {
        if ($this->naOrder === null) {
            return null;
        }

        $aTidy = [];
        foreach ($this->naOrder as $mItem) {
            if ($mItem instanceof V) {
                $aTidy[] = (string)$mItem;
            } else {
                $sSort   = $mItem[0] === '-' ? 'DESC' : 'ASC';
                $mItem   = substr($mItem, 1);
                $mItem   = strpos($mItem, '.') === false ? "{$this->sQuote}{$mItem}{$this->sQuote}" : $mItem;
                $aTidy[] = "$mItem $sSort";
            }
        }

        return implode(',', $aTidy);
    }

    /**
     * @return int|null
     */
    protected function parseLimit()
    {
        return $this->__parse_limit_offset($this->niLimit);
    }

    /**
     * @return int|null
     */
    protected function parseOffset()
    {
        return $this->__parse_limit_offset($this->niOffset);
    }

    /**
     * @param $mV
     *
     * @return int|null
     */
    protected function __parse_limit_offset($mV)
    {
        return $mV === null ? null : (int)$mV;
    }

    /**
     * @param array $aMap
     *
     * @return string
     */
    protected function parseUpdateMap(array $aMap)
    {
        $aTidy = [];
        foreach ($aMap as $sK => $mV) {
            if (strpos($sK, '.') === false) {
                $sK = "{$this->sQuote}$sK{$this->sQuote}";
            }

            $aTidy[] = "$sK = " . (is_string($mV) ? "'$mV'" : (string)$mV);
        }

        return implode(',', $aTidy);
    }
}
