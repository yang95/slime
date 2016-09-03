<?php
namespace SlimeInterface\RDBMS\ORM;

interface CollectionInterface extends \Iterator, \Countable
{
    /**
     * @return bool
     */
    public function isEmpty();

    /**
     * @return ItemInterface
     */
    public function first();

    /**
     * @return array
     */
    public function toArray();
}
