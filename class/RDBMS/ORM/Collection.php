<?php
namespace Slime\RDBMS\ORM;

use Slime\Container\ContainerObject;
use SlimeInterface\RDBMS\ORM\CollectionInterface;
use SlimeInterface\RDBMS\ORM\ItemInterface;

class Collection extends ContainerObject implements CollectionInterface
{
    /** @var array */
    protected $aOrgData = [];

    /** @var array */
    protected $aData = [];

    /** @var Model */
    protected $Model;

    /** @var string */
    protected $sModelItem;

    /** @var bool */
    private $_bHasReachedFinal = false;

    public function __construct(array $aData, $sModelItem, Model $Model)
    {
        $this->aOrgData   = $this->aData = $aData;
        $this->sModelItem = $sModelItem;
        $this->Model      = $Model;
    }

    public function buildData()
    {
        foreach ($this->aOrgData as $iK => $aOne) {
            $Obj = new $this->sModelItem($aOne, $this->Model, $this);
            if ($Obj instanceof Item) {
                $Obj->__init__($this->_getContainer());
            }
            $this->aData[$iK] = $Obj;
        }
    }

    /**
     * Return the current element
     *
     * @link  http://php.net/manual/en/iterator.current.php
     * @return ItemInterface
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->aData);
    }

    /**
     * Move forward to next element
     *
     * @link  http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $mRS = next($this->aData);
        if ($mRS === false) {
            $this->_bHasReachedFinal = true;
        }
    }

    /**
     * Return the key of the current element
     *
     * @link  http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->aData);
    }

    /**
     * Checks if current position is valid
     *
     * @link  http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *        Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->_bHasReachedFinal === false;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link  http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->aData);
        $this->_bHasReachedFinal = false;
    }

    /**
     * Count elements of an object
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *        </p>
     *        <p>
     *        The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->aData);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->aData) === 0;
    }

    /**
     * @return null|ItemInterface
     */
    public function first()
    {
        if (count($this->aData) === 0) {
            return null;
        }
        $this->rewind();
        return $this->current();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->aOrgData;
    }
}
