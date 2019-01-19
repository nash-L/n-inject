<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/19
 * Time: 1:27
 */

namespace NashInject\Type;


use NashInject\Exception\InjectorTypeException;

class IntType extends FloatType
{
    /**
     * @param $data
     * @return int
     * @throws InjectorTypeException
     */
    public function validate($data)
    {
        if (is_string($data) && is_numeric($data) && (strpos($data, '.') === false)) {
            return intval($data);
        } elseif (is_int($data)) {
            return $data;
        }
        throw new InjectorTypeException('Type Error', InjectorTypeException::ERROR_TYPE);
    }
}
