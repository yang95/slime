<?php
namespace Slime\Route\Router;

use Slime\Container\Container;
use Slime\Container\ContainerObject;
use Slime\Http\ServerResponse;
use Slime\Http\ServerRequest;
use SlimeInterface\Route\RouterInterface;
use Composer\Autoload\ClassLoader;

/**
 * Class REST
 *
 * @package Slime\Route\Router
 *
 * @method array _getArgv() _getArgv()
 * @method Container _getContainer() _getContainer()
 * @method ClassLoader _getAutoLoader() _getAutoLoader()
 */
class CLI extends ContainerObject implements RouterInterface
{
    protected $aConf;

    public function __construct(array $aConf)
    {
        $this->aConf = array_merge(
            [
                'controller_pre'  => 'C_',
                'controller_post' => '',
                'action_pre'      => 'action',
                'action_post'     => '',
                'namespace'       => ''
            ],
            $aConf
        );
    }

    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @param bool $bHit
     *
     * @return bool
     */
    public function run(&$bHit)
    {
        $bGoOn = false;
        $aArgv = $this->_getArgv();
        if (!isset($aArgv[1])) {
        }
        if (!isset($aArgv[2])) {
        }
        $sController = $aArgv[1];
        $sMethod     = $aArgv[2];
        $sController = sprintf('%s\\%s%s%s',
            $this->aConf['namespace'],
            $this->aConf['controller_pre'],
            $sController,
            $this->aConf['controller_post']
        );
        $sMethod     = sprintf("%s%s%s",
            $this->aConf['action_pre'],
            $sMethod,
            $this->aConf['action_post']
        );

        // @todo 对于某个 controller do cache
        $bsFile = $this->_getAutoLoader()->findFile($sController);
        if ($bsFile === false) {
            fprintf(STDERR, "There is no controller[$sController]");
            goto END;
        }

        $aMethod = [];
        $Ref     = new \ReflectionClass($sController);
        foreach ($Ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $Method) {
            $aMethod[$Method->getName()] = true;
        }

        if (!isset($aMethod[$sMethod])) {
            fprintf(STDERR, "There is no method[$sMethod] in controller[$sController]");
            goto END;
        }

        $Obj = $Ref->newInstance($this->_getContainer());
        unset($aArgv[0]);
        unset($aArgv[1]);
        unset($aArgv[2]);
        $aParam = array_values($aArgv);
        $bHit   = true;

        if (isset($aMethod['__before__'])) {
            if ($Obj->__before__($sMethod, $aParam) === false) {
                goto END;
            }
        }

        if (call_user_func_array([$Obj, $sMethod], $aParam) === false) {
            goto END;
        }

        if (isset($aMethod['__after__'])) {
            $Obj->__after__($sMethod, $aParam);
        }

        END:
        return $bGoOn;
    }
}