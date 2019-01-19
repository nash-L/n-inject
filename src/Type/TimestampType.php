<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/19
 * Time: 14:22
 */

namespace NashInject\Type;


use NashInject\Exception\InjectorTypeException;

class TimestampType extends IntType
{
    /**
     * @param $data
     * @return int
     * @throws \NashInject\Exception\InjectorTypeException
     */
    public function validate($data)
    {
        $data = parent::validate($data);
        if ($data > 0 && $data <= 2145887999) {
            return $data;
        }
        throw new InjectorTypeException('Type Error', InjectorTypeException::ERROR_TYPE);
    }
}
