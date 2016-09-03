<?php
namespace SlimeInterface\Route;

interface RouterInterface
{
    /**
     * @param bool $bHit
     *
     * @return bool
     */
    public function run(&$bHit);
}