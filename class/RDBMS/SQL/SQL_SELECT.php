<?php
namespace Slime\RDBMS\SQL;

use SlimeInterface\RDBMS\SQL\SelectInterface;

/**
 * Class SQL_SELECT
 *
 * @package Slime\RDBMS\SQL
 * @author  smallslime@gmail.com
 */
class SQL_SELECT extends SQLAbstract implements SelectInterface
{
    /** @var array|null */
    protected $naGroupBy = null;

    /** @var array|null */
    protected $naField = null;

    /** @var Condition|null */
    protected $nHaving = null;

    /** @var array|null */
    protected $naDFTField = null;

    /** @var int|null */
    protected $niLockType = null;

    /** @var string|null */
    protected $nsAlias = null;

    /**
     * @param array $aDFTField
     *
     * @return self
     */
    public function defaultField(array $aDFTField = [])
    {
        $this->naDFTField = $aDFTField;
        return $this;
    }

    /**
     * @param string|V $sField_V
     *
     * multi param as param one
     *
     * @return self
     */
    public function fields($sField_V)
    {
        $this->naField = func_get_args();
        return $this;
    }

    /**
     * @param string|V $sGroupBy_V
     *
     * multi param as param one
     *
     * @return self
     */
    public function groupBy($sGroupBy_V)
    {
        $aArr            = func_get_args();
        $this->naGroupBy = $this->naGroupBy === null ? $aArr : array_merge($this->naGroupBy, $aArr);
        return $this;
    }

    /**
     * @param Condition $Condition
     *
     * @return self
     */
    public function having($Condition)
    {
        $this->nHaving = $Condition;
        return $this;
    }

    /**
     * @param string $sAlias
     *
     * @return self
     */
    public function alias($sAlias)
    {
        $this->nsAlias = $sAlias;
        return $this;
    }

    /**
     * @return self
     */
    public function lockForUpdate()
    {
        $this->niLockType = 1;
        return $this;
    }

    /**
     * @return self
     */
    public function lockInShareMode()
    {
        $this->niLockType = 0;
        return $this;
    }

    /**
     * @return string
     */
    protected function parseField()
    {
        if (empty($this->naField)) {
            return $this->naDFTField === null ?
                '*' : $this->sQuote . implode("{$this->sQuote},{$this->sQuote}", $this->naDFTField) . $this->sQuote;
        }
        $aField = [];
        foreach ($this->naField as $mItem) {
            $aField[] = is_string($mItem) && strpos($mItem, '.') === false ?
                "{$this->sQuote}$mItem{$this->sQuote}" : (string)$mItem;
        }
        return implode(',', $aField);
    }

    /**
     * @return null|string
     */
    protected function parseGroupBy()
    {
        if ($this->naGroupBy === null) {
            return null;
        }

        $aGroupBy = [];
        foreach ($this->naGroupBy as $mItem) {
            $aGroupBy[] = is_string($mItem) && strpos($mItem, '.') === false ?
                "{$this->sQuote}$mItem{$this->sQuote}" : (string)$mItem;
        }
        return implode(',', $aGroupBy);
    }

    /**
     * @return null|string
     */
    protected function parseLockType()
    {
        if ($this->niLockType === null) {
            return null;
        }

        return $this->niLockType === 1 ? 'FOR UPDATE' : 'LOCK IN SHARE MODE';
    }

    public function __toString()
    {
        return sprintf(
            "SELECT %s FROM %s%s%s%s%s%s%s%s%s",
            $this->parseField(),
            $this->parseTable(),
            ($nsJoin = $this->parseJoin()) === null ? '' : " $nsJoin",
            $this->nWhere === null ? '' : ' WHERE ' . $this->parseCondition($this->nWhere),
            $this->naGroupBy === null ? '' : ' GROUP BY ' . implode(',', $this->naGroupBy),
            $this->nHaving === null ? '' : ' HAVING ' . $this->parseCondition($this->nHaving),
            ($nsOrder = $this->parseOrder()) === null ? '' : " ORDER BY {$nsOrder}",
            $this->niLimit === null ? '' : " LIMIT {$this->niLimit}",
            $this->niOffset === null ? '' : " OFFSET {$this->niOffset}",
            ($nsLock = $this->parseLockType()) === null ? '' : " $nsLock"
        );
    }

    /**
     * @return int
     */
    public function getSQLType()
    {
        return self::SQL_TYPE_SELECT;
    }

    /**
     * @return null|string
     */
    public function getAlias()
    {
        return $this->nsAlias;
    }
}
