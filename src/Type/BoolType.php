<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/19
 * Time: 2:29
 */

namespace NashInject\Type;


use NashInject\Exception\InjectorTypeException;

class BoolType extends InjectorType
{
    /**
     * @param $data
     * @return bool
     * @throws InjectorTypeException
     */
    public function validate($data)
    {
        if ($data === '1' || $data === 1) {
            return true;
        } elseif ($data === '0' || $data === 0) {
            return false;
        }
        throw new InjectorTypeException(InjectorTypeException::ERROR_TYPE, 'Type Error');
    }
}
