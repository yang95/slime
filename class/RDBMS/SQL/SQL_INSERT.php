<?php
namespace Slime\RDBMS\SQL;

use SlimeInterface\RDBMS\SQL\InsertInterface;

/**
 * Class SQL_INSERT
 *
 * @package Slime\RDBMS\SQL
 * @author  smallslime@gmail.com
 */
class SQL_INSERT extends SQLAbstract implements InsertInterface
{
    /** @var int|null */
    protected $niType = null;

    /** @var array|null */
    protected $naWhere = null;

    /** @var array|null */
    protected $naKey = null;

    /** @var array */
    protected $aData = [];

    /** @var array */
    protected $aUpdateMap = [];

    /**
     * @param int        $iType   SQLInterface::TYPE_IGNORE / SQLInterface::TYPE_UPDATE / SQLInterface::TYPE_REPLACE
     * @param array|null $naWhere if iType is TYPE_UPDATE , declare as condition(kv map)
     *
     * @return self
     */
    public function asOtherType($iType, $naWhere = null)
    {
        $this->niType = $iType;
        if ($naWhere !== null) {
            $this->naWhere = $naWhere;
        }

        return $this;
    }

    /**
     * @param array $aKey
     *
     * @return self
     */
    public function keys($aKey)
    {
        $this->naKey = $aKey;

        return $this;
    }

    /**
     * @param array|string|SQL_SELECT $m_aValue_sValue_SQLSEL
     *
     * @return self
     */
    public function values($m_aValue_sValue_SQLSEL)
    {
        $this->aData[] = $m_aValue_sValue_SQLSEL;

        return $this;
    }

    /**
     * @param $aKV
     *
     * @return self
     */
    public function setMap($aKV)
    {
        return $this->keys(array_keys($aKV))->values(array_values($aKV));
    }

    /**
     * @param array $aMap
     *
     * @return self
     */
    public function updateData($aMap)
    {
        $this->aUpdateMap = $aMap;

        return $this;
    }

    /**
     * @return null|string
     */
    protected function parseData()
    {
        $aTidy = [];
        foreach ($this->aData as $mRow) {
            if (is_array($mRow)) {
                $aV = [];
                foreach ($mRow as $mV) {
                    $aV[] = is_string($mV) ? "'$mV'" : (string)$mV;
                }
            } else {
                $aV[] = (string)$mRow;
            }

            $aTidy[] = implode(',', $aV);
        }

        switch (count($aTidy)) {
            case 0:
                return null;
            case 1:
                return $aTidy[0];
            default:
                return implode('),(', $aTidy);
        }
    }

    /**
     * @return null|string
     */
    protected function parseKey()
    {
        if ($this->naKey === null) {
            return null;
        }

        $aTidy = [];
        foreach ($this->naKey as $mItem) {
            $aTidy[] = is_string($mItem) && strpos($mItem, '.') === false ?
                "{$this->sQuote}$mItem{$this->sQuote}" : (string)$mItem;
        }

        return '(' . implode(',', $aTidy) . ')';
    }

    public function __toString()
    {
        return sprintf(
            "%s INTO %s%s VALUES (%s)%s",
            $this->niType === self::TYPE_IGNORE ? 'INSERT IGNORE' : ($this->niType === self::TYPE_REPLACE ? 'REPLACE' : 'INSERT'),
            $this->parseTable(),
            ($nsKey = $this->parseKey()) === null ? '' : " $nsKey",
            $this->parseData(),
            $this->niType === self::TYPE_UPDATE ?
                (' ON DUPLICATE KEY UPDATE ' . $this->parseUpdateMap($this->aUpdateMap)) : ''
        );
    }

    /**
     * @return int
     */
    public function getSQLType()
    {
        return self::SQL_TYPE_INSERT;
    }
}