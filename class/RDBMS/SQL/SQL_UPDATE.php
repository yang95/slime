<?php
namespace Slime\RDBMS\SQL;

use SlimeInterface\RDBMS\SQL\UpdateInterface;

/**
 * Class SQL_UPDATE
 *
 * @package Slime\RDBMS\SQL
 * @author  smallslime@gmail.com
 */
class SQL_UPDATE extends SQLAbstract implements UpdateInterface
{
    /** @var array */
    protected $aDataMap = [];

    /**
     * @param array $aKV
     *
     * @return self
     */
    public function setMap($aKV)
    {
        $this->aDataMap = $aKV;

        return $this;
    }

    public function __toString()
    {
        return sprintf(
            'UPDATE %s%s SET %s%s%s%s%s',
            $this->parseTable(),
            ($nsJoin = $this->parseJoin()) === null ? '' : " $nsJoin",
            $this->parseUpdateMap($this->aDataMap),
            $this->nWhere === null ? '' : ' WHERE ' . $this->parseCondition($this->nWhere),
            ($nsOrder = $this->parseOrder()) === null ? '' : " ORDER BY {$nsOrder}",
            $this->niLimit === null ? '' : " LIMIT {$this->niLimit}",
            $this->niOffset === null ? '' : " OFFSET {$this->niOffset}"
        );
    }

    /**
     * @return int
     */
    public function getSQLType()
    {
        return self::SQL_TYPE_UPDATE;
    }
}