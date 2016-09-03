<?php
namespace SlimeInterface\RDBMS\SQL;

interface InsertInterface extends SQLInterface
{
    const TYPE_IGNORE  = 1;
    const TYPE_UPDATE  = 2;
    const TYPE_REPLACE = 3;

    /**
     * @param int        $iType   SQLInterface::TYPE_IGNORE / SQLInterface::TYPE_UPDATE / SQLInterface::TYPE_REPLACE
     * @param array|null $naWhere if iType is TYPE_UPDATE , declare as condition(kv map)
     *
     * @return self
     */
    public function asOtherType($iType, $naWhere = null);

    /**
     * @param array $aKey
     *
     * @return self
     */
    public function keys($aKey);

    /**
     * @param array|string|SQLInterface $m_aValue_sValue_SQLSEL
     *
     * @return self
     */
    public function values($m_aValue_sValue_SQLSEL);

    /**
     * @param array $aKV
     *
     * @return self
     */
    public function setMap($aKV);

    /**
     * @param array $aMap
     *
     * @return self
     */
    public function updateData($aMap);

    /**
     * @param string $sTable
     *
     * @return self
     */
    public function table($sTable);

    /**
     * @return mixed
     */
    public function run();
}
