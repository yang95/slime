<?php
namespace Slime\RDBMS\SQL;

/**
 * Class SQL
 *
 * @package Slime\RDBMS\SQL
 * @author  smallslime@gmail.com
 */
class SQLFactory
{
    const TYPE_MYSQL  = 1;
    const TYPE_SQLITE = 2;
    const TYPE_PGSQL  = 3;

    /** @var string */
    protected $sQuote;

    /** @var int */
    protected $iSQLType;

    /** @var null|object */
    protected $nModel = null;

    public static function create($iSQLType, $nModel = null)
    {
        return new self($iSQLType, $nModel);
    }

    private function __construct($iSQLType, $nModel = null)
    {
        $this->iSQLType = $iSQLType;
        if ($nModel !== null) {
            $this->nModel = $nModel;
        }
        switch ($iSQLType) {
            default:
                $this->sQuote = '`';
        }
    }

    /**
     * @return SQL_SELECT
     */
    public function select()
    {
        return new SQL_SELECT($this->sQuote, $this->nModel);
    }

    /**
     * @return SQL_INSERT
     */
    public function insert()
    {
        return new SQL_INSERT($this->sQuote, $this->nModel);
    }

    /**
     * @return SQL_UPDATE
     */
    public function update()
    {
        return new SQL_UPDATE($this->sQuote, $this->nModel);
    }

    /**
     * @return SQL_DELETE
     */
    public function delete()
    {
        return new SQL_DELETE($this->sQuote, $this->nModel);
    }
}
