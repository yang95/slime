<?php
namespace Slime\Container\Exception;

use SlimeInterface\Container\Exception\NotFoundExceptionInterface;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
