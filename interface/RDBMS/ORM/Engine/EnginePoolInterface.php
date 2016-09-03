<?php
namespace SlimeInterface\RDBMS\ORM\Engine;

use SlimeInterface\RDBMS\ORM\ModelInterface;
use SlimeInterface\RDBMS\SQL\SQLInterface;

interface EnginePoolInterface
{
    /**
     * @param string              $sExpectKey
     * @param ModelInterface      $Model
     * @param SQLInterface|string $m_sSQL_SQL
     *
     * @return mixed
     */
    public function getInst($sExpectKey, ModelInterface $Model, $m_sSQL_SQL);
}
