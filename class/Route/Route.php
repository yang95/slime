<?php
namespace Slime\Route;

use Slime\Container\Container;
use Slime\Container\ContainerObject;
use Slime\Http\ServerRequest;
use Slime\Http\ServerResponse;
use SlimeInterface\Route\RouteInterface;
use SlimeInterface\Route\RouterInterface;

/**
 * Class Route
 *
 * @package Slime
 *
 * @method ServerRequest _getRequest() _getRequest()
 * @method ServerResponse _getResponse() _getResponse()
 * @method Container _getContainer() _getContainer()
 */
class Route extends ContainerObject implements RouteInterface
{
    /** @var RouterInterface[] */
    protected $aRouter;

    public function addRouter(RouterInterface $Router)
    {
        $this->aRouter[] = $Router;
    }

    public function run()
    {
        $bHit = false;
        foreach ($this->aRouter as $Router) {
            if ($Router->run($bHit) === false) {
                break;
            }
        }
    }
}
