<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/19
 * Time: 1:36
 */

namespace NashInject\Type;


use NashInject\Exception\InjectorTypeException;

class FloatType extends InjectorType
{
    /**
     * @param $data
     * @return float
     * @throws InjectorTypeException
     */
    public function validate($data)
    {
        if (is_string($data) && is_numeric($data)) {
            return floatval($data);
        } elseif (is_float($data)) {
            return $data;
        }
        throw new InjectorTypeException(InjectorTypeException::ERROR_TYPE, 'Type Error');
    }
}
