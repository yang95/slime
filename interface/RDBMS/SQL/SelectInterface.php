<?php
namespace SlimeInterface\RDBMS\SQL;

interface SelectInterface extends SQLInterface
{
    /**
     * @param array $aDFTField
     *
     * @return self
     */
    public function defaultField(array $aDFTField = []);

    /**
     * @param string $sField
     *
     * multi param as param one
     *
     * @return self
     */
    public function fields($sField);

    /**
     * @param string $sGroupBy
     *
     * multi param as param one
     *
     * @return self
     */
    public function groupBy($sGroupBy);

    /**
     * @param ConditionInterface $Condition
     *
     * @return self
     */
    public function having($Condition);

    /**
     * @param string $sAlias
     *
     * @return self
     */
    public function alias($sAlias);

    /**
     * @return self
     */
    public function lockForUpdate();

    /**
     * @return self
     */
    public function lockInShareMode();

    /**
     * @return null|string
     */
    public function getAlias();

    /**
     * @param string $sTable
     *
     * @return self
     */
    public function table($sTable);

    /**
     * @param ConditionInterface $Condition
     *
     * @return self
     */
    public function where(ConditionInterface $Condition);

    /**
     * @param string|SelectInterface $sTable_SQLSEL
     * @param ConditionInterface     $Condition
     * @param string                 $sJoinType
     *
     * @return self
     */
    public function join($sTable_SQLSEL, ConditionInterface $Condition, $sJoinType = 'INNER');

    /**
     * @param string $sOrder
     *
     * @return self
     */
    public function orderBy($sOrder);

    /**
     * @param int $iLimit
     *
     * @return self
     */
    public function limit($iLimit);

    /**
     * @param int $iOffset
     *
     * @return self
     */
    public function offset($iOffset);

    /**
     * @return mixed
     */
    public function run();
}
