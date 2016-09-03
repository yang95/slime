<?php
namespace SlimeInterface\RDBMS\ORM;

use SlimeInterface\RDBMS\SQL\DeleteInterface;
use SlimeInterface\RDBMS\SQL\InsertInterface;
use SlimeInterface\RDBMS\SQL\SelectInterface;
use SlimeInterface\RDBMS\SQL\SQLInterface;
use SlimeInterface\RDBMS\SQL\UpdateInterface;

interface ModelInterface
{
    /**
     * @return string
     */
    public function getPK();

    /**
     * @return string
     */
    public function getFK();

    /**
     * @return string
     */
    public function getTable();

    /**
     * @param $mPK
     *
     * @return ItemInterface|null
     */
    public function findByPK($mPK);

    /**
     * @return ItemInterface
     */
    public function createEmptyItem();

    /**
     * @return SelectInterface
     */
    public function select();

    /**
     * @return UpdateInterface
     */
    public function update();

    /**
     * @return InsertInterface
     */
    public function insert();

    /**
     * @return DeleteInterface
     */
    public function delete();

    /**
     * @param SQLInterface|string $SQL
     *
     * @return int|CollectionInterface|string int:update/delete(effect rows); string:insert(last insert id);
     *                                        Collection: select
     */
    public function run(SQLInterface $SQL);

    /**
     * @param string $sModelName
     *
     * @return ModelInterface
     */
    public function getOtherModel($sModelName);
}