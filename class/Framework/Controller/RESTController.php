<?php
namespace Slime\Framework\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use SlimeInterface\Config\ConfigureInterface;
use SlimeInterface\Container\ContainerInterface;
use SlimeInterface\Http\ServerResponseInterface;

abstract class RESTController
{
    /** @var ContainerInterface */
    protected $C;

    /** @var ServerRequestInterface */
    protected $REQ;

    /** @var ServerResponseInterface */
    protected $RESP;

    /** @var ConfigureInterface */
    protected $Config;

    /** @var LoggerInterface */
    protected $Log;

    protected $iErr  = 0;
    protected $sErr  = '';
    protected $aData = [];

    public function __construct(ContainerInterface $C)
    {
        $this->C      = $C;
        $this->REQ    = $C->get('Request');
        $this->RESP   = $C->get('Response');
        $this->Config = $C->get('Config');
        $this->Log    = $C->get('Log');
    }

    public function __after__()
    {
        $this->RESP
            ->setHeader('Content-Type', 'text/json; charset=utf-8')
            ->setBody(
                json_encode([
                    'code' => $this->iErr,
                    'msg'  => $this->sErr,
                    'data' => count($this->aData) === 0 ? new \ArrayObject() : $this->aData
                ], JSON_UNESCAPED_UNICODE)
            );
    }
}
