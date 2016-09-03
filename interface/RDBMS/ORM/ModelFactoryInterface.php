<?php
namespace SlimeInterface\RDBMS\ORM;

interface ModelFactoryInterface
{
    /**
     * @param string $sModelName
     *
     * @return ModelInterface
     */
    public function getModel($sModelName);

    public function __get($sName);
}