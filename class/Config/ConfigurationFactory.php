<?php
namespace Slime\Config;

use InvalidArgumentException;
use SlimeInterface\Config\ConfigureInterface;

/**
 * Class ConfigurationFactory
 *
 * @package Slime\Config
 * @author  smallslime@gmail.com
 */
final class ConfigurationFactory
{
    /**
     * @param string $sAdaptor
     * @param array  $aArgv
     *
     * @return ConfigureInterface
     */
    public static function create($sAdaptor, array $aArgv = [])
    {
        $Ref = new \ReflectionClass($sAdaptor);
        $Obj = $Ref->newInstanceArgs($aArgv);
        if (!$Obj instanceof ConfigureInterface) {
            throw new InvalidArgumentException();
        }
        return $Obj;
    }
}