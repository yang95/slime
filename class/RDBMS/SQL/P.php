<?php
namespace Slime\RDBMS\SQL;

/**
 * Class P
 *
 * @package Slime\RDBMS\SQL
 * @author  smallslime@gmail.com
 */
class P implements \ArrayAccess
{
    public static function make(array $aData)
    {
        return new self($aData);
    }

    protected $aData;
    protected $aDataUse = [];

    private function __construct(array $aData)
    {
        $this->aData = $aData;
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
        if (!isset($this->aData[$offset])) {
            throw new \RuntimeException();
        }
        $this->aDataUse[$offset] = true;
        return V::make(":$offset");
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
        $this->aData[$offset] = $value;
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
        unset($this->aData[$offset]);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $aData = [];
        foreach ($this->aDataUse as $sK => $bV) {
            $aData[":$sK"] = $this->aData[$sK];
        }
        unset($this->aDataUse);
        return $aData;
    }
}