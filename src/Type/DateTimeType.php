<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/19
 * Time: 14:19
 */

namespace NashInject\Type;


use NashInject\Exception\InjectorTypeException;

class DateTimeType extends InjectorType
{
    protected $date = null, $time = null, $timestamp = null;
    /**
     * @param $data
     * @return mixed
     * @throws InjectorTypeException
     */
    public function validate($data)
    {
        if (is_string($data)) {
            if (preg_match('/^((?!0000)\d{4}-((0[13578]|1[02])-(0[1-9]|[12]\d|3[01])|(02-(0[1-9]|1\d|2[0-8]))|(0[469]|11)-(0[1-9]|[12]\d|30))|(\d{2}(0[48]|[2468][048]|[13579][26])|(0[48]|[2468][048]|[13579][26])00)-02-29) ([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $data)) {
                return $data;
            }
        }
        throw new InjectorTypeException('Type Error', InjectorTypeException::ERROR_TYPE);
    }

    /**
     * @return DateType
     * @throws InjectorTypeException
     */
    public function getDate()
    {
        if (is_null($this->date)) {
            list($dateStr, $timeStr) = explode(' ', $this->getData());
            $this->date = new DateType($dateStr, $this->injector);
            $this->time = new TimeType($timeStr, $this->injector);
        }
        return $this->date;
    }

    /**
     * @return TimeType
     * @throws InjectorTypeException
     */
    public function getTime()
    {
        if (is_null($this->time)) {
            list($dateStr, $timeStr) = explode(' ', $this->getData());
            $this->date = new DateType($dateStr, $this->injector);
            $this->time = new TimeType($timeStr, $this->injector);
        }
        return $this->time;
    }

    /**
     * @return TimestampType|null
     * @throws InjectorTypeException
     */
    public function toTimestamp()
    {
        if (is_null($this->timestamp)) {
            $this->timestamp = new TimestampType(strtotime($this->getData()), $this->injector);
        }
        return $this->timestamp;
    }
}
