<?php
namespace Slime\Config;

use SlimeInterface\Config\ConfigureInterface;

class PHPConfiguration implements ConfigureInterface
{
    protected $sCurrentFile = '';
    protected $sDefaultFile = '';
    protected $bDefault     = false;
    protected $bLoaded      = false;
    protected $aData        = [];

    /**
     * @param string      $sBaseDir
     * @param string      $sCurrentFile
     * @param null|string $nsDefaultFile
     */
    public function __construct($sBaseDir, $sCurrentFile, $nsDefaultFile = null)
    {
        $this->sCurrentFile = $sBaseDir . '/' . $sCurrentFile;
        $this->sDefaultFile = $nsDefaultFile === null ? $this->sCurrentFile : $sBaseDir . '/' . $nsDefaultFile;
        $this->bDefault     = $this->sCurrentFile === $this->sDefaultFile;
    }

    /**
     * @return bool
     */
    public function load()
    {
        $this->aData = $this->bDefault ?
            require $this->sCurrentFile :
            array_merge(require $this->sCurrentFile, require $this->sDefaultFile);

        $this->bLoaded = true;
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
        if (!$this->bLoaded) {
            $this->load();
        }

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
        if (!$this->bLoaded) {
            $this->load();
        }

        return isset($this->aData[$offset]) ? $this->aData[$offset] : null;
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
        throw new \RuntimeException('It\'s not supported!');
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
        throw new \RuntimeException('It\'s not supported!');
    }
}