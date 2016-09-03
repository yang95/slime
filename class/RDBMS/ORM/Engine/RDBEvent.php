<?php
namespace Slime\RDBMS\ORM\Engine;

class RDBEvent
{
    const EV_BEFORE_RUN = 'slime:rdb:engine:run_before';
    const EV_AFTER_RUN = 'slime:rdb:engine:run_after';

    const EV_BEFORE_STMT_RUN = 'slime:rdb:engine:stmt_run_before';
    const EV_AFTER_STMT_RUN  = 'slime:rdb:engine:stmt_run_after';
}