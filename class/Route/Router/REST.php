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
 * @method ServerRequest _getRequest() _getRequest()
 * @method ServerResponse _getResponse() _getResponse()
 * @method Container _getContainer() _getContainer()
 * @method ClassLoader _getAutoLoader() _getAutoLoader()
 */
class REST extends ContainerObject implements RouterInterface
{
    protected $aConf;

    public function __construct(array $aConf)
    {
        $this->aConf = array_merge(
            [
                'controller_pre'  => '',
                'controller_post' => 'Controller',
                'action_pre'      => '',
                'action_post'     => '',
                'namespace'       => ''
            ],
            $aConf
        );
    }

    /**
     * @param bool $bHit
     *
     * @return bool
     */
    public function run(&$bHit)
    {
        $bGoOn = false;
        $REQ   = $this->_getRequest();
        $RESP  = $this->_getResponse();

        $aBlock = explode('/', strtolower(trim($REQ->getUri()->getPath(), '/')));
        if (count($aBlock) < 2 || !preg_match('/v\d+/', $aBlock[0])) {
            $RESP->setStatus(400)->setBody('url path block error');
            goto END;
        }
        $sVersion = ucfirst(array_shift($aBlock));
        if (!preg_match('/V\d+/', $sVersion)) {
            $RESP->setStatus(400)->setBody('url path 1 must be v\\d+');
            goto END;
        }

        $sMethod = strtolower($REQ->getMethod());
        if (substr($aBlock[count($aBlock) - 1], -7) === '.action') {
            $sLast = array_pop($aBlock);
            if (count($aBlock) === 0) {
                $RESP->setStatus(400)->setBody('url path block error');
                goto END;
            }
            $sMethod = substr($sLast, 0, strlen($sLast) - 7) . '_' . $sMethod;
        }
        $sMethod = "{$this->aConf['action_pre']}$sMethod{$this->aConf['action_post']}";

        $aActionDetail = [];
        $aParam        = [];
        if (($iBlock = count($aBlock)) > 0) {
            $i = 0;
            do {
                if ($aBlock[$i][0] === '_') {
                    $aActionDetail[] = substr($aBlock[$i], 1);
                    $i += 1;
                    continue;
                }
                if (!isset($aBlock[$i + 1])) {
                    $aActionDetail[] = $aBlock[$i];
                    break;
                }
                $aParam[$aBlock[$i]] = $aBlock[$i + 1];
                $aActionDetail[]     = $aBlock[$i];
                $i += 2;
            } while ($i < $iBlock);
        }

        $aLast = explode('-', array_pop($aActionDetail));
        if (count($aActionDetail) > 0) {
            $sMethod .= '_by_' . implode('_', $aActionDetail);
        }

        if (is_string($this->aConf['namespace'])) {
            $sNS = $this->aConf['namespace'];
        } else {
            $sHost = $REQ->getServerParams()['HTTP_HOST'];
            if (isset($this->aConf['namespace'][$sHost])) {
                $sNS = $this->aConf['namespace'][$sHost];
            } else {
                if (!isset($this->aConf['namespace']['default'])) {
                    $RESP->setStatus(404)->setBody("UnSupport host[$sHost]");
                    goto END;
                }
                $sNS = $this->aConf['namespace']['default'];
            }
        }

        $sLastBlock  = array_pop($aLast);
        $sController = sprintf(
            '%s\\%s\\%s%s%s%s',
            $sNS,
            $sVersion,
            count($aLast) === 0 ? '' :
                (
                    implode('\\',
                        array_map(
                            function ($sStr) {
                                return str_replace(' ', '', ucwords(str_replace('_', ' ', $sStr)));
                            },
                            $aLast
                        )
                    ) . '\\'
                ),
            $this->aConf['controller_pre'],
            str_replace(' ', '', ucwords(str_replace('_', ' ', $sLastBlock))),
            $this->aConf['controller_post']
        );

        $bsFile = $this->_getAutoLoader()->findFile($sController);
        if ($bsFile === false) {
            $RESP->setStatus(404)->setBody("There is no controller[$sController]");
            goto END;
        }

        // @todo 对于某个 controller do cache
        $aMethod = [];
        $Ref     = new \ReflectionClass($sController);
        foreach ($Ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $Method) {
            $aMethod[$Method->getName()] = true;
        }

        if (!isset($aMethod[$sMethod])) {
            $RESP->setStatus(404)->setBody("There is no method[$sMethod] in controller[$sController]");
            goto END;
        }

        $Obj  = $Ref->newInstance($this->_getContainer());
        $bHit = true;
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