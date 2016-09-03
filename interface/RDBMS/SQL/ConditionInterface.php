<?php
namespace SlimeInterface\RDBMS\SQL;

interface ConditionInterface
{
    /**
     * @return array
     */
    public function getData();

    /**
     * @return string
     */
    public function getRel();

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function eq($sK, $mV);

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function notEq($sK, $mV);

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function gt($sK, $mV);

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function gtEq($sK, $mV);

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function lt($sK, $mV);

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function ltEq($sK, $mV);

    /**
     * @param string $sK
     * @param mixed  $mV1
     * @param mixed  $mV2
     *
     * @return self
     */
    public function between($sK, $mV1, $mV2);

    /**
     * @param string $sK
     * @param array  $aV
     *
     * @return self
     */
    public function in($sK, array $aV);

    /**
     * @param string $sK
     * @param array  $aV
     *
     * @return self
     */
    public function notIn($sK, array $aV);

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function like($sK, $mV);

    /**
     * @param string $sK
     *
     * @return self
     */
    public function isNull($sK);

    /**
     * @param string $sK
     *
     * @return self
     */
    public function isNotNull($sK);

    /**
     * @param ConditionInterface $Condition
     *
     * @return self
     */
    public function sub(ConditionInterface $Condition);
}
