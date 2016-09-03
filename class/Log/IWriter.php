<?php
namespace Slime\Log;

/**
 * Interface IWriter
 *
 * @package Slime\Log
 * @author  smallslime@gmail.com
 */
interface IWriter
{
    public function acceptData($aRow);
}