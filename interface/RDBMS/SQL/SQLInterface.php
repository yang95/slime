<?php
namespace SlimeInterface\RDBMS\SQL;

interface SQLInterface
{
    const SQL_TYPE_SELECT = 1;
    const SQL_TYPE_INSERT = 2;
    const SQL_TYPE_UPDATE = 3;
    const SQL_TYPE_DELETE = 4;

    public function __toString();

    /**
     * @return int
     */
    public function getSQLType();

    /**
     * @param array|object $m_a_Map
     *
     * @return self
     */
    public function bind($m_a_Map);

    /**
     * @return array|null
     */
    public function getBind();
}
