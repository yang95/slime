<?php
namespace Slime\Redis;

class RedisEvent
{
    const EV_BEFORE_EXEC = 'slime:redis:exec_before';
    const EV_AFTER_EXEC = 'slime:redis:exec_after';
}