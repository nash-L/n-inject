<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/15
 * Time: 23:13
 */

namespace NashInject\Exception;


use Exception;

class InjectorException extends Exception
{
    const ERROR_CLASS_NOT_EXISTS = 1;
    const ERROR_CON_NOT_MAKE_PARAM = 2;
    const ERROR_PARAM = 3;
    const ERROR_DEPENDENT_CYCLE = 4;
}
