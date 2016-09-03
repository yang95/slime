<?php
namespace Slime\Http;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use InvalidArgumentException;

class Stream implements StreamInterface
{
    /** @var null|resource */
    protected $nrRes;

    /** @var null|string */
    protected $nsStream;

    /**
     * @param string|resource $mStream
     * @param string          $sMode Mode with which to open stream
     *
     * @throws InvalidArgumentException
     */
    public function __construct($mStream, $sMode = 'r')
    {
        $brHandle = false;
        if (is_string($mStream)) {
            $this->nsStream = $mStream;
            $brHandle       = @fopen($mStream, $sMode);
        }

        if ($brHandle === false) {
            throw new InvalidArgumentException('Invalid stream reference provided');
        }

        if (!is_resource($brHandle) || get_resource_type($brHandle) !== 'stream') {
            throw new InvalidArgumentException(
                'Invalid stream provided; must be a string stream identifier or stream resource'
            );
        }

        $this->nrRes = $brHandle;
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        $sStr = '';
        if (!$this->isReadable()) {
            goto END;
        }

        try {
            $this->rewind();
            $sStr = $this->getContents();
        } catch (RuntimeException $e) {
        }

        END:
        return $sStr;
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        if ($this->nrRes === null) {
            return;
        }

        $resource = $this->detach();
        fclose($resource);
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $resource    = $this->nrRes;
        $this->nrRes = null;
        return $resource;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        $niSize = null;
        if ($this->nrRes === null) {
            goto END;
        }
        $niSize = fstat($this->nrRes)['size'];

        END:
        return $niSize;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        if ($this->nrRes === null) {
            throw new RuntimeException('No resource available; cannot tell position');
        }
        $biPos = ftell($this->nrRes);
        if (!is_int($biPos)) {
            throw new RuntimeException('Error occurred during tell operation');
        }

        return $biPos;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        $bRS = true;
        if ($this->nrRes) {
            $bRS = feof($this->nrRes);
        }

        return $bRS;
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        $bRS = false;
        if ($this->nrRes !== null) {
            $aMeta = stream_get_meta_data($this->nrRes);
            $bRS   = (bool)$aMeta['seekable'];
        }

        return $bRS;
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     *
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *                    based on the seek offset. Valid values are identical to the built-in
     *                    PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *                    offset bytes SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     *
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->nrRes) {
            throw new RuntimeException('No resource available; cannot seek position');
        }

        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }

        if (fseek($this->nrRes, $offset, $whence) !== 0) {
            throw new RuntimeException('Error seeking within stream');
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see  seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        return $this->seek(0);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        static $aChar = ['x' => true, 'w' => true, 'c' => true, 'a' => true, '+' => true];
        $bRS = false;
        if ($this->nrRes !== null) {
            $sMode = stream_get_meta_data($this->nrRes)['mode'];
            for ($i = 0, $iLen = strlen($sMode); $i < $iLen; $i++) {
                if (isset($aChar[$sMode[$i]])) {
                    $bRS = true;
                    break;
                }
            }
        }

        return $bRS;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     *
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string)
    {
        if ($this->nrRes === null) {
            throw new RuntimeException('No resource available; cannot write');
        }
        if (!$this->isWritable()) {
            throw new RuntimeException('Stream is not writable');
        }
        $biRS = fwrite($this->nrRes, $string);
        if ($biRS === false) {
            throw new RuntimeException('Error writing to stream');
        }

        return $biRS;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        static $aChar = ['r' => true, '+' => true];

        $bRS = false;
        if ($this->nrRes !== null) {
            $sMode = stream_get_meta_data($this->nrRes)['mode'];
            for ($i = 0, $iLen = strlen($sMode); $i < $iLen; $i++) {
                if (isset($aChar[$sMode[$i]])) {
                    $bRS = true;
                    break;
                }
            }
        }

        return $bRS;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *                    them. Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     *
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        if ($this->nrRes === null) {
            throw new RuntimeException('No resource available; cannot read');
        }
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }
        $bsRS = fread($this->nrRes, $length);
        if ($bsRS === false) {
            throw new RuntimeException('Error reading stream');
        }

        return $bsRS;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents()
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }
        $this->rewind();
        $bsRS = stream_get_contents($this->nrRes);
        if ($bsRS === false) {
            throw new RuntimeException('Error reading from stream');
        }

        return $bsRS;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     *
     * @param string $key Specific metadata to retrieve.
     *
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        $mRS = null;
        if ($this->nrRes === null) {
            goto END;
        }
        if ($key === null) {
            $mRS = stream_get_meta_data($this->nrRes);
            goto END;
        }

        $aMeta = stream_get_meta_data($this->nrRes);
        if (!isset($aMeta[$key])) {
            goto END;
        }
        $mRS = $aMeta[$key];

        END:
        return $mRS;
    }
}
