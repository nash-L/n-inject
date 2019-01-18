<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/19
 * Time: 1:25
 */

namespace NashInject\Exception;


use Throwable;
use Exception;

class InjectorTypeException extends Exception
{
    const ERROR_TYPE = 1;

    function __construct(int $code = 0, string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
