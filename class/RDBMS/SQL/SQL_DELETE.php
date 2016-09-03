<?php
namespace Slime\RDBMS\SQL;

use SlimeInterface\RDBMS\SQL\DeleteInterface;

/**
 * Class SQL_DELETE
 *
 * @package Slime\RDBMS\SQL
 * @author  smallslime@gmail.com
 */
class SQL_DELETE extends SQLAbstract implements DeleteInterface
{
    public function __toString()
    {
        return sprintf(
            "DELETE FROM %s%s%s%s%s",
            $this->parseTable(),
            ($nsJoin = $this->parseJoin()) === null ? '' : " $nsJoin",
            $this->nWhere === null ? '' : ' WHERE ' . $this->parseCondition($this->nWhere),
            $this->naOrder === null ? '' : ' ORDER BY ' . implode(' ', $this->naOrder),
            $this->niLimit === null ? '' : " LIMIT {$this->niLimit}",
            $this->niOffset === null ? '' : " OFFSET {$this->niOffset}"
        );
    }

    /**
     * @return int
     */
    public function getSQLType()
    {
        return self::SQL_TYPE_DELETE;
    }
}