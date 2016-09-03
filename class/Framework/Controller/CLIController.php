<?php
namespace Slime\Framework\Controller;

use Slime\Container\Container;

abstract class CLIController
{
    /** @var Container */
    protected $C;

    protected $iErr  = 0;
    protected $sErr  = '';
    protected $aData = [];

    public function __construct(Container $C)
    {
        $this->C = $C;
    }

    public function __before__()
    {
    }

    public function __after__()
    {
    }
}
