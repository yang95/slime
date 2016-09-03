<?php
namespace Slime\Container;

use Slime\Container\Exception\NotFoundException;
use SlimeInterface\Container\ContainerInterface;
use SlimeInterface\Container\Exception\ContainerExceptionInterface;
use SlimeInterface\Container\Exception\NotFoundExceptionInterface;

class Container implements ContainerInterface, \ArrayAccess
{
    protected $aCB;
    protected $aData;
    protected $aDataMarked;

    /**
     * @param array $aConf
     *
     * @return self
     */
    public static function createFromConfig(array $aConf)
    {
        $Obj = new self();
        foreach ($aConf as $sK => $sV) {
            $Obj[$sK] = $sV;
        }

        return $Obj;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for this identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (!isset($this->aData[$id])) {
            if (!isset($this->aCB[$id])) {
                throw new NotFoundException();
            }
            $this->aData[$id] = $mV = call_user_func($this->aCB[$id], $this);
        } else {
            $mV = $this->aData[$id];
        }
        if (!isset($this->aDataMarked[$id])) {
            if ($mV instanceof ContainerObject) {
                $mV->__init__($this);
            }
            $this->aDataMarked[$id] = true;
        }

        return $mV;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return isset($this->aData[$id]) || isset($this->aCB[$id]);
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
        return $this->has($offset);
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
        return $this->get($offset);
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
        if ($value instanceof \Closure) {
            $this->aCB[$offset] = $value;
        } else {
            $this->aData[$offset] = $value;
        }
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
        if (isset($this->aData[$offset])) {
            unset($this->aData[$offset]);
        }
        if (isset($this->aCB[$offset])) {
            unset($this->aCB[$offset]);
        }
    }
}
