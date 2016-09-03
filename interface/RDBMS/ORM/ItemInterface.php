<?php
namespace SlimeInterface\RDBMS\ORM;

interface ItemInterface extends \ArrayAccess
{
    public function save();

    public function delete();

    public function toArray();
}
