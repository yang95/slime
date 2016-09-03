<?php
namespace SlimeInterface\RDBMS\SQL;

interface UpdateInterface extends SQLInterface
{
    /**
     * @param array $aKV
     *
     * @return self
     */
    public function setMap($aKV);

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
