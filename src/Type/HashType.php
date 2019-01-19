<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/19
 * Time: 2:43
 */

namespace NashInject\Type;


use NashInject\Exception\InjectorTypeException;

class HashType extends InjectorType
{
    /**
     * @param $data
     * @return array
     * @throws InjectorTypeException
     */
    public function validate($data)
    {
        if (is_string($data)) {
            $data = json_decode($data);
        } elseif (is_array($data)) {
            $data = json_decode(json_encode($data));
        }
        if (is_object($data)) {
            return (array)$data;
        }
        throw new InjectorTypeException('Type Error', InjectorTypeException::ERROR_TYPE);
    }
}
