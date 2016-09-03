<?php
namespace Slime\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    public static function createFromCGI()
    {
        $aHeader = [];
        foreach ($_SERVER as $sK => $sV) {
            if (substr($sK, 0, 5) === 'HTTP_') {
                $aHeader[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($sK, 5)))))][] = $sV;
            }
        }

        return new self(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            explode('/', $_SERVER['SERVER_PROTOCOL'])[1],
            $aHeader,
            new Stream('php://input'),
            $_SERVER,
            $_COOKIE,
            $_GET,
            $_FILES,
            $_POST
        );
    }

    /** @var array */
    protected $aServer = [];

    /** @var array */
    protected $aCookie = [];

    /** @var array */
    protected $aQuery = [];

    /** @var array */
    protected $aUpload = [];

    /** @var null|array|object */
    protected $mParsedBody;

    /** @var array */
    protected $aAttr = [];

    public function __construct(
        $sMethod,
        $sRequestTarget,
        $sProtocolVersion,
        array $aHeader,
        StreamInterface $Body,
        array $aServer,
        array $aCookie,
        array $aQuery,
        array $aUpload,
        $mParsedBody,
        array $aAttr = []
    ) {
        $this->aServer     = $aServer;
        $this->aCookie     = $aCookie;
        $this->aQuery      = $aQuery;
        $this->aUpload     = $aUpload;
        $this->mParsedBody = $mParsedBody;
        $this->aAttr       = $aAttr;

        parent::__construct($sMethod, $sRequestTarget, $sProtocolVersion, $aHeader, $Body);
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->aServer;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->aCookie;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     *
     * @return self
     */
    public function withCookieParams(array $cookies)
    {
        $New          = clone $this;
        $New->aCookie = $cookies;
        return $New;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        return $this->aQuery;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *                     $_GET.
     *
     * @return self
     */
    public function withQueryParams(array $query)
    {
        $this->aQuery = $query;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        return $this->aUpload;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     *
     * @return self
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $New          = clone $this;
        $New->aUpload = $uploadedFiles;
        return $New;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody()
    {
        return $this->mParsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *                                typically be in an array or object.
     *
     * @return self
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data)
    {
        $New              = clone $this;
        $New->mParsedBody = $data;
        return $New;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->aAttr;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     *
     * @param string $name    The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->aAttr[$name]) ? $this->aAttr[$name] : $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     *
     * @param string $name  The attribute name.
     * @param mixed  $value The value of the attribute.
     *
     * @return self
     */
    public function withAttribute($name, $value)
    {
        $New               = clone $this;
        $New->aAttr[$name] = $value;
        return $New;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     *
     * @param string $name The attribute name.
     *
     * @return self
     */
    public function withoutAttribute($name)
    {
        $New = clone $this;
        if (isset($New->aAttr[$name])) {
            unset($New->aAttr[$name]);
        }
        return $New;
    }

    /**
     * @return string
     */
    public function getClientIP()
    {
        $aParam = $this->getServerParams();
        if (isset($aParam['HTTP_X_FORWARDED_FOR'])) {
            $sIP = $aParam['HTTP_X_FORWARDED_FOR'];
            $biSplitPos = strpos($sIP, '.');
            if ($biSplitPos !== false) {
                $sIP = substr($sIP, 0, $biSplitPos);
            }
            $sIP = trim($sIP);
        } elseif (isset($aParam['HTTP_CLIENT_IP'])) {
            $sIP = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($aParam['REMOTE_ADDR'])) {
            $sIP = $aParam['REMOTE_ADDR'];
        } else {
            $sIP = '';
        }
        return $sIP;
    }
}
