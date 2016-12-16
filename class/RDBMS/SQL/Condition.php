<?php
namespace Slime\RDBMS\SQL;

use SlimeInterface\RDBMS\SQL\ConditionInterface;

/**
 * Class Condition
 *
 * @package Slime\Component\RDBMS\DBAL
 */
class Condition implements ConditionInterface
{
    protected $sRel;
    protected $aData;

    public static function asOr()
    {
        return new self('OR');
    }

    public static function asAnd()
    {
        return new self('AND');
    }

    private function __construct($sRel)
    {
        $this->sRel  = $sRel;
        $this->aData = [];
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->aData;
    }

    /**
     * @return string
     */
    public function getRel()
    {
        return $this->sRel;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function eq($sK, $mV)
    {
        $this->aData[] = [$sK, '=', $mV];
        return $this;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function notEq($sK, $mV)
    {
        $this->aData[] = [$sK, '<>', $mV];
        return $this;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function gt($sK, $mV)
    {
        $this->aData[] = [$sK, '>', $mV];
        return $this;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function gtEq($sK, $mV)
    {
        $this->aData[] = [$sK, '>=', $mV];
        return $this;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function lt($sK, $mV)
    {
        $this->aData[] = [$sK, '<', $mV];
        return $this;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function ltEq($sK, $mV)
    {
        $this->aData[] = [$sK, '<=', $mV];
        return $this;
    }

    /**
     * @param string $sK
     * @param mixed  $mV1
     * @param mixed  $mV2
     *
     * @return self
     */
    public function between($sK, $mV1, $mV2)
    {
        $this->aData[] = [$sK, 'BETWEEN', [$mV1, $mV2]];
        return $this;
    }

    /**
     * @param string $sK
     * @param array  $aV
     *
     * @return self
     */
    public function in($sK, array $aV)
    {
        $this->aData[] = [$sK, 'IN', $aV];
        return $this;
    }

    /**
     * @param string $sK
     * @param array  $aV
     *
     * @return self
     */
    public function notIn($sK, array $aV)
    {
        $this->aData[] = [$sK, 'NOT IN', $aV];
        return $this;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function like($sK, $mV)
    {
        $this->aData[] = [$sK, 'LIKE', $mV];
        return $this;
    }
    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function notLike($sK, $mV)
    {
        $this->aData[] = [$sK, 'NOT LIKE', $mV];
        return $this;
    }
    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return self
     */
    public function notLike($sK, $mV)
    {
        $this->aData[] = [$sK, 'NOT LIKE', $mV];
        return $this;
    }

    /**
     * @param string $sK
     *
     * @return self
     */
    public function isNull($sK)
    {
        $this->aData[] = [$sK, 'IS', 'NULL'];
        return $this;
    }

    /**
     * @param string $sK
     *
     * @return self
     */
    public function isNotNull($sK)
    {
        $this->aData[] = [$sK, 'IS NOT', 'NULL'];
        return $this;
    }

    /**
     * @param ConditionInterface $Condition
     *
     * @return self
     */
    public function sub(ConditionInterface $Condition)
    {
        $this->aData[] = $Condition;

        return $this;
    }
}
