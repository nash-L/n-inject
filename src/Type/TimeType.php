<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/19
 * Time: 14:16
 */

namespace NashInject\Type;


use NashInject\Exception\InjectorTypeException;

class TimeType extends InjectorType
{
    /**
     * @param $data
     * @return mixed
     * @throws InjectorTypeException
     */
    public function validate($data)
    {
        if (is_string($data)) {
            if (preg_match('/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $data)) {
                return $data;
            }
        }
        throw new InjectorTypeException('Type Error', InjectorTypeException::ERROR_TYPE);
    }
}
