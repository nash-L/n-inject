<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/19
 * Time: 2:31
 */

namespace NashInject\Type;


use NashInject\Exception\InjectorTypeException;

class ArrayType extends InjectorType
{
    /**
     * @param $data
     * @return mixed
     * @throws InjectorTypeException
     */
    public function validate($data)
    {
        if (is_string($data)) {
            $data = json_decode($data);
        } elseif (is_array($data)) {
            $data = json_decode(json_encode($data));
        }
        if (is_array($data)) {
            return $data;
        }
        throw new InjectorTypeException('Type Error', InjectorTypeException::ERROR_TYPE);
    }
}
