<?php
namespace Slime\RDBMS\ORM;

use Slime\Container\ContainerObject;
use Slime\RDBMS\SQL\Condition;

class Item extends ContainerObject implements \ArrayAccess
{
    /** @var array */
    protected $aData = [];

    /** @var array */
    protected $aNewData = [];

    /** @var Model */
    protected $Model;

    /** @var null|Collection */
    protected $nCollection;

    public function __construct(array $aData, Model $Model, Collection $nCollection = null)
    {
        $this->aData       = $aData;
        $this->Model       = $Model;
        $this->nCollection = $nCollection;
    }

    public function __get($sK)
    {
        return $this->aData[$sK];
    }

    public function __set($sK, $mV)
    {
        if (isset($this->aData[$sK]) && ($this->aData[$sK] === $mV)) {
            return;
        }

        $this->aNewData[$sK] = $mV;
    }

    /**
     * @param mixed $mCB callback for SQL
     *
     * @return mixed 1. null for no exec 2. insert : int(lastID)/false ; update : int(effect rows)
     */
    public function save($mCB = null)
    {
        if (empty($this->aNewData)) {
            return null;
        }
        if (count($this->aData) === 0) {
            $SQL = $this->Model->insert()->setMap($this->aNewData);
            if ($mCB !== null) {
                call_user_func($mCB, $SQL);
            }
            $mRS = $SQL->run();
            if ($mRS !== false) {
                $this->aData = $this->aNewData;
            }
        } else {
            $sPKName = $this->Model->getPK();
            $SQL     = $this->Model->update()
                ->setMap($this->aNewData)
                ->where(
                    Condition::asAnd()->eq($sPKName, $this->aData[$sPKName])
                );
            if ($mCB !== null) {
                call_user_func($mCB, $SQL);
            }
            $mRS = $SQL->run();
            if ($mRS > 0) {
                $this->aData = array_merge($this->aData, $this->aNewData);
            }
        }

        unset($this->aNewData);
        return $mRS;
    }

    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->aData[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->aNewData[$offset]);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->aData;
    }
}