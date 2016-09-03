<?php
namespace Slime\Memcached;

class MemcachedEvent
{
    const EV_BEFORE_EXEC = 'slime:memcached:exec_before';
    const EV_AFTER_EXEC = 'slime:memcached:exec_after';
}