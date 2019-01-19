<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/19
 * Time: 12:24
 */

namespace NashInject\Type;


use NashInject\Exception\InjectorTypeException;

class DateType extends InjectorType
{
    /**
     * @param $data
     * @throws InjectorTypeException
     */
    public function validate($data)
    {
        if (is_string($data)) {
            if (preg_match('/^((?!0000)\d{4}-((0[13578]|1[02])-(0[1-9]|[12]\d|3[01])|(02-(0[1-9]|1\d|2[0-8]))|(0[469]|11)-(0[1-9]|[12]\d|30))|(\d{2}(0[48]|[2468][048]|[13579][26])|(0[48]|[2468][048]|[13579][26])00)-02-29)$/', $data)) {
                return $data;
            }
        }
        throw new InjectorTypeException('Type Error', InjectorTypeException::ERROR_TYPE);
    }

    /**
     * @return DateTimeType
     * @throws InjectorTypeException
     */
    public function getStartDateTime()
    {
        return new DateTimeType($this->getData() . ' 00:00:00', $this->injector);
    }

    /**
     * @return DateTimeType
     * @throws InjectorTypeException
     */
    public function getEndDateTime()
    {
        return new DateTimeType($this->getData() . ' 23:59:59', $this->injector);
    }
}
