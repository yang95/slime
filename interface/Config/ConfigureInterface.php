<?php
namespace SlimeInterface\Config;

interface ConfigureInterface extends \ArrayAccess
{
    /**
     * @return bool
     */
    public function load();
}