<?php
namespace Slime\RDBMS\SQL;

/**
 * Class V
 *
 * @package Slime\RDBMS\SQL
 * @author  smallslime@gmail.com
 */
class V
{
    public static function make($mV)
    {
        return new self($mV);
    }

    protected $mV;

    private function __construct($mV)
    {
        $this->mV = $mV;
    }

    public function __toString()
    {
        return (string)$this->mV;
    }
}